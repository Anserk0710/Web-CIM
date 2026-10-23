<?php // path: app/Jobs/SendOutboxNotification.php

namespace App\Jobs;

use App\Enums\InquiryType;
use App\Mail\OperationalNotificationMail;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\Inquiry;
use App\Models\NotificationOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendOutboxNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $outboxId,
    ) {}

    /**
     * Laravel retry backoff, in seconds.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        $record = DB::transaction(function (): ?NotificationOutbox {
            $outbox = NotificationOutbox::query()
                ->whereKey($this->outboxId)
                ->lockForUpdate()
                ->first();

            if ($outbox === null) {
                return null;
            }

            if (in_array(
                $outbox->status,
                [
                    NotificationOutbox::SENT,
                    NotificationOutbox::FAILED,
                ],
                true,
            )) {
                return null;
            }

            if (
                $outbox->next_attempt_at !== null
                && $outbox->next_attempt_at->isFuture()
            ) {
                return null;
            }

            $processingIsRecent = $outbox->processing_at !== null
                && $outbox->processing_at->greaterThan(
                    now()->subMinutes(
                        (int) config(
                            'notifications.processing_timeout_minutes',
                            15,
                        ),
                    ),
                );

            if (
                $outbox->status === NotificationOutbox::PROCESSING
                && $processingIsRecent
            ) {
                return null;
            }

            $outbox->forceFill([
                'status' => NotificationOutbox::PROCESSING,
                'attempts' => $outbox->attempts + 1,
                'queued_at' => null,
                'processing_at' => now(),
                'last_attempt_at' => now(),
            ])->save();

            return $outbox->fresh();
        }, attempts: 3);

        if ($record === null) {
            return;
        }

        try {
            $recipient = $this->recipientFor($record);

            if (
                $recipient === null
                || filter_var(
                    $recipient,
                    FILTER_VALIDATE_EMAIL,
                ) === false
            ) {
                throw new RuntimeException(
                    'Notification recipient is not configured.',
                );
            }

            $record->forceFill([
                'recipient' => $recipient,
            ])->save();

            [$headline, $details] = $this->messageFor(
                $record,
            );

            Mail::to($recipient)->send(
                new OperationalNotificationMail(
                    outboxId: $record->id,
                    headline: $headline,
                    details: $details,
                ),
            );

            $record->forceFill([
                'status' => NotificationOutbox::SENT,
                'sent_at' => now(),
                'processing_at' => null,
                'queued_at' => null,
                'next_attempt_at' => null,
                'last_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $record->refresh();

            $maxAttempts = (int) config(
                'notifications.max_attempts',
                3,
            );

            $isSynchronousQueue = config('queue.default') === 'sync';

            $permanentlyFailed = $isSynchronousQueue
                || $record->attempts >= $maxAttempts;

            $nextAttempt = match ($record->attempts) {
                1 => 30,
                2 => 120,
                default => 300,
            };

            $record->forceFill([
                'status' => $permanentlyFailed
                    ? NotificationOutbox::FAILED
                    : NotificationOutbox::PENDING,

                'processing_at' => null,
                'queued_at' => null,

                'next_attempt_at' => $permanentlyFailed
                    ? null
                    : now()->addSeconds($nextAttempt),

                // Simpan kelas exception saja; jangan menyimpan stack trace.
                'last_error' => class_basename($exception)
                    .': Email delivery failed.',
            ])->save();

            Log::error('CIM notification delivery failed.', [
                'outbox_id' => $record->id,
                'event_type' => $record->event_type,
                'attempt' => $record->attempts,
                'exception' => get_class($exception),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        NotificationOutbox::query()
            ->whereKey($this->outboxId)
            ->where(
                'status',
                '!=',
                NotificationOutbox::SENT,
            )
            ->update([
                'status' => NotificationOutbox::FAILED,
                'processing_at' => null,
                'queued_at' => null,
                'next_attempt_at' => null,
                'last_error' => class_basename(
                    $exception ?? new RuntimeException(),
                ).': Maximum delivery attempts exhausted.',
                'updated_at' => now(),
            ]);
    }

    private function recipientFor(
        NotificationOutbox $record,
    ): ?string {
        return match ($record->event_type) {
            'INQUIRY_SUBMITTED' => $this->nullableConfig(
                'notifications.inquiry_recipient',
            ),

            'CAREER_APPLICATION_SUBMITTED' => $this->nullableConfig(
                'notifications.career_application_recipient',
            ),

            default => null,
        };
    }

    private function nullableConfig(
        string $key,
    ): ?string {
        $value = trim((string) config($key, ''));

        return $value === '' ? null : $value;
    }

    /**
     * @return array{
     *   0: string,
     *   1: array<string, string|int>
     * }
     */
    private function messageFor(
        NotificationOutbox $record,
    ): array {
        if ($record->entity_type === 'Inquiry') {
            $inquiry = Inquiry::query()
                ->find($record->entity_id);

            if ($inquiry === null) {
                throw new RuntimeException(
                    'Inquiry notification entity no longer exists.',
                );
            }

            $type = $inquiry->type instanceof InquiryType
                ? $inquiry->type->value
                : (string) $inquiry->type;

            return [
                'New contact inquiry #'.$inquiry->id,

                [
                    'Inquiry ID' => $inquiry->id,
                    'Type' => $type,
                    'Status' => $inquiry->status->value,
                    'CMS URL' => rtrim(
                        (string) config('app.url'),
                        '/',
                    ).'/admin/inquiries/'.$inquiry->id,
                ],
            ];
        }

        if ($record->entity_type === 'CareerApplication') {
            $application = CareerApplication::query()
                ->find($record->entity_id);

            if ($application === null) {
                throw new RuntimeException(
                    'Career application notification entity no longer exists.',
                );
            }

            $career = Career::query()
                ->find($application->career_id);

            return [
                'New career application #'.$application->id,

                [
                    'Application ID' => $application->id,
                    'Vacancy' => $career?->title ?? 'Vacancy unavailable',
                    'Status' => $application->status->value,
                    'CMS URL' => rtrim(
                        (string) config('app.url'),
                        '/',
                    ).'/admin/applications/'.$application->id,
                ],
            ];
        }

        throw new RuntimeException(
            'Unsupported notification entity type.',
        );
    }
}
