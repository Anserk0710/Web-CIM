<?php // path: app/Services/Notifications/NotificationOutboxService.php

namespace App\Services\Notifications;

use App\Jobs\SendOutboxNotification;
use App\Models\CareerApplication;
use App\Models\Inquiry;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationOutboxService
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function inquirySubmitted(
        Inquiry $inquiry,
    ): NotificationOutbox {
        return $this->enqueue(
            eventKey: 'inquiry.submitted:'.$inquiry->id,
            eventType: 'INQUIRY_SUBMITTED',
            entityType: 'Inquiry',
            entityId: (int) $inquiry->id,
            payload: [
                'type' => $inquiry->type->value,
            ],
        );
    }

    public function applicationSubmitted(
        CareerApplication $application,
    ): NotificationOutbox {
        return $this->enqueue(
            eventKey: 'career-application.submitted:'.$application->id,
            eventType: 'CAREER_APPLICATION_SUBMITTED',
            entityType: 'CareerApplication',
            entityId: (int) $application->id,
            payload: [
                'career_id' => $application->career_id,
            ],
        );
    }

    /**
     * Dispatch a pending event. Safe to call from both the outbox callback
     * and the recovery command.
     */
    public function dispatchIfDue(
        int $outboxId,
    ): bool {
        $shouldDispatch = DB::transaction(
            function () use ($outboxId): bool {
                $record = NotificationOutbox::query()
                    ->whereKey($outboxId)
                    ->lockForUpdate()
                    ->first();

                if (
                    $record === null
                    || $record->status !== NotificationOutbox::PENDING
                ) {
                    return false;
                }

                if (
                    $record->next_attempt_at !== null
                    && $record->next_attempt_at->isFuture()
                ) {
                    return false;
                }

                $queuedIsRecent = $record->queued_at !== null
                    && $record->queued_at->greaterThan(
                        now()->subMinutes(
                            (int) config(
                                'notifications.queued_timeout_minutes',
                                10,
                            ),
                        ),
                    );

                if ($queuedIsRecent) {
                    return false;
                }

                $record->forceFill([
                    'queued_at' => now(),
                ])->save();

                return true;
            },
            attempts: 3,
        );

        if (! $shouldDispatch) {
            return false;
        }

        try {
            SendOutboxNotification::dispatch(
                $outboxId,
            )->onQueue('notifications');

            return true;
        } catch (Throwable $exception) {
            NotificationOutbox::query()
                ->whereKey($outboxId)
                ->where(
                    'status',
                    NotificationOutbox::PENDING,
                )
                ->update([
                    'queued_at' => null,
                    'updated_at' => now(),
                ]);

            Log::error('CIM notification dispatch failed.', [
                'outbox_id' => $outboxId,
                'exception' => get_class($exception),
            ]);

            // Jangan melempar error ke submission request.
            return false;
        }
    }

    public function retryFailed(
        NotificationOutbox $record,
        User $actor,
    ): bool {
        $retry = DB::transaction(
            function () use ($record, $actor): bool {
                $locked = NotificationOutbox::query()
                    ->whereKey($record->id)
                    ->lockForUpdate()
                    ->first();

                if (
                    $locked === null
                    || $locked->status !== NotificationOutbox::FAILED
                ) {
                    return false;
                }

                $locked->forceFill([
                    'status' => NotificationOutbox::PENDING,
                    'attempts' => 0,
                    'queued_at' => null,
                    'processing_at' => null,
                    'next_attempt_at' => now(),
                    'last_attempt_at' => null,
                    'sent_at' => null,
                    'last_error' => null,
                ])->save();

                $this->audit->record(
                    action: 'notification.retry_requested',
                    entity: $locked,
                    changes: [
                        'outbox_id' => $locked->id,
                        'event_type' => $locked->event_type,
                    ],
                    actor: $actor,
                );

                return true;
            },
            attempts: 3,
        );

        if ($retry) {
            DB::afterCommit(
                fn () => $this->dispatchIfDue(
                    (int) $record->id,
                ),
            );
        }

        return $retry;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function enqueue(
        string $eventKey,
        string $eventType,
        string $entityType,
        int $entityId,
        array $payload,
    ): NotificationOutbox {
        $record = NotificationOutbox::query()
            ->firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'event_type' => $eventType,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'recipient' => null,
                    'payload' => $payload,
                    'status' => NotificationOutbox::PENDING,
                    'attempts' => 0,
                ],
            );

        if ($record->wasRecentlyCreated) {
            $outboxId = (int) $record->id;

            DB::afterCommit(
                fn () => $this->dispatchIfDue(
                    $outboxId,
                ),
            );
        }

        return $record;
    }
}
