<?php // path: app/Console/Commands/RunPrivacyRetention.php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Enums\InquiryStatus;
use App\Models\CareerApplication;
use App\Models\Inquiry;
use App\Services\Audit\AuditService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RunPrivacyRetention extends Command
{
    protected $signature = 'privacy:purge {--dry-run}';

    protected $description = 'Anonymize due career applications and inquiries according to the CIM retention policy.';

    public function __construct(
        private readonly AuditService $audit,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->info('DRY RUN — no records or files will be changed.');

            $this->line(
                'Career applications due: '
                .$this->applicationsDueQuery()->count(),
            );

            $this->line(
                'Inquiries due: '
                .$this->inquiriesDueQuery()->count(),
            );

            $this->line(
                'Audit logs to scrub: '
                .$this->auditLogsToScrubQuery()->count(),
            );

            $this->line(
                'Audit logs to delete: '
                .$this->auditLogsToDeleteQuery()->count(),
            );

            return self::SUCCESS;
        }

        $applicationCount = 0;
        $inquiryCount = 0;
        $failureCount = 0;

        $this->applicationsDueQuery()
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (
                &$applicationCount,
                &$failureCount,
            ): void {
                foreach ($rows as $row) {
                    try {
                        if ($this->anonymizeApplication(
                            (int) $row->id,
                        )) {
                            $applicationCount++;
                        }
                    } catch (Throwable $exception) {
                        $failureCount++;

                        Log::error(
                            'Career application retention failed.',
                            [
                                'application_id' => $row->id,
                                'exception' => get_class($exception),
                            ],
                        );

                        $this->error(
                            'Career application #'.$row->id
                            .' could not be anonymized.',
                        );
                    }
                }
            });

        $this->inquiriesDueQuery()
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (
                &$inquiryCount,
                &$failureCount,
            ): void {
                foreach ($rows as $row) {
                    try {
                        if ($this->anonymizeInquiry(
                            (int) $row->id,
                        )) {
                            $inquiryCount++;
                        }
                    } catch (Throwable $exception) {
                        $failureCount++;

                        Log::error(
                            'Inquiry retention failed.',
                            [
                                'inquiry_id' => $row->id,
                                'exception' => get_class($exception),
                            ],
                        );

                        $this->error(
                            'Inquiry #'.$row->id
                            .' could not be anonymized.',
                        );
                    }
                }
            });

        $now = now();

        $auditScrubbed = $this->auditLogsToScrubQuery()
            ->update([
                'ip' => null,
                'user_agent' => null,
            ]);

        $auditDeleted = $this->auditLogsToDeleteQuery()
            ->delete();

        $this->newLine();
        $this->info('Privacy retention completed.');
        $this->line('Career applications anonymized: '.$applicationCount);
        $this->line('Inquiries anonymized: '.$inquiryCount);
        $this->line('Audit logs scrubbed: '.$auditScrubbed);
        $this->line('Audit logs deleted: '.$auditDeleted);
        $this->line('Failed records: '.$failureCount);

        return $failureCount === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function applicationsDueQuery(): Builder
    {
        return CareerApplication::query()
            ->whereNull('anonymized_at')
            ->whereNotNull('retention_due_at')
            ->where('retention_due_at', '<=', now())
            ->whereIn('status', [
                ApplicationStatus::Rejected->value,
                ApplicationStatus::Hired->value,
            ]);
    }

    private function inquiriesDueQuery(): Builder
    {
        $resolvedBefore = now()->subMonthsNoOverflow(12);
        $spamBefore = now()->subDays(30);

        return Inquiry::query()
            ->whereNull('anonymized_at')
            ->where(function (Builder $query) use (
                $resolvedBefore,
                $spamBefore,
            ): void {
                $query
                    ->where(function (Builder $resolved) use (
                        $resolvedBefore,
                    ): void {
                        $resolved
                            ->where(
                                'status',
                                InquiryStatus::Resolved->value,
                            )
                            ->whereNotNull('resolved_at')
                            ->where(
                                'resolved_at',
                                '<=',
                                $resolvedBefore,
                            );
                    })
                    ->orWhere(function (Builder $spam) use (
                        $spamBefore,
                    ): void {
                        $spam
                            ->where(
                                'status',
                                InquiryStatus::Spam->value,
                            )
                            ->whereNotNull('spam_marked_at')
                            ->where(
                                'spam_marked_at',
                                '<=',
                                $spamBefore,
                            );
                    });
            });
    }

    private function anonymizeApplication(
        int $applicationId,
    ): bool {
        return DB::transaction(
            function () use ($applicationId): bool {
                $application = CareerApplication::query()
                    ->whereKey($applicationId)
                    ->lockForUpdate()
                    ->first();

                if (
                    $application === null
                    || $application->anonymized_at !== null
                    || $application->retention_due_at === null
                    || $application->retention_due_at->isFuture()
                    || ! in_array(
                        $application->status->value,
                        [
                            ApplicationStatus::Rejected->value,
                            ApplicationStatus::Hired->value,
                        ],
                        true,
                    )
                ) {
                    return false;
                }

                $this->deletePrivateCv($application);

                DB::table('application_notes')
                    ->where(
                        'application_id',
                        $application->id,
                    )
                    ->delete();

                // Free-text reason can contain personal data.
                DB::table('application_status_events')
                    ->where(
                        'application_id',
                        $application->id,
                    )
                    ->update([
                        'reason' => null,
                    ]);

                $application->forceFill([
                    'full_name' => null,
                    'email' => null,
                    'email_normalized' => null,
                    'phone' => null,
                    'introduction' => null,
                    'portfolio_url' => null,
                    'linkedin_url' => null,

                    'cv_disk' => null,
                    'cv_path' => null,
                    'cv_original_name' => null,
                    'cv_mime' => null,
                    'cv_size' => null,
                    'cv_sha256' => null,

                    'anonymized_at' => now(),
                    'lock_version' => $application->lock_version + 1,
                ])->save();

                DB::table('privacy_deletion_ledger')
                    ->insertOrIgnore([
                        'entity_type' => 'CareerApplication',
                        'entity_id' => $application->id,
                        'category' => 'CAREER_APPLICATION_PII',
                        'deleted_at' => now(),
                    ]);

                $this->audit->record(
                    action: 'privacy.application_anonymized',
                    entity: $application,
                    changes: [
                        'category' => 'CAREER_APPLICATION_PII',
                        'anonymized' => true,
                    ],
                    actor: null,
                );

                return true;
            },
            attempts: 3,
        );
    }

    private function anonymizeInquiry(
        int $inquiryId,
    ): bool {
        return DB::transaction(
            function () use ($inquiryId): bool {
                $inquiry = Inquiry::query()
                    ->whereKey($inquiryId)
                    ->lockForUpdate()
                    ->first();

                if (
                    $inquiry === null
                    || $inquiry->anonymized_at !== null
                ) {
                    return false;
                }

                $now = now();

                $resolvedDue = $inquiry->status
                        === InquiryStatus::Resolved
                    && $inquiry->resolved_at !== null
                    && $inquiry->resolved_at->lessThanOrEqualTo(
                        $now->copy()->subMonthsNoOverflow(12),
                    );

                $spamDue = $inquiry->status
                        === InquiryStatus::Spam
                    && $inquiry->spam_marked_at !== null
                    && $inquiry->spam_marked_at->lessThanOrEqualTo(
                        $now->copy()->subDays(30),
                    );

                if (! $resolvedDue && ! $spamDue) {
                    return false;
                }

                DB::table('inquiry_notes')
                    ->where(
                        'inquiry_id',
                        $inquiry->id,
                    )
                    ->delete();

                $inquiry->forceFill([
                    'name' => null,
                    'email' => null,
                    'phone' => null,
                    'organization' => null,
                    'message' => null,

                    'anonymized_at' => $now,
                    'lock_version' => $inquiry->lock_version + 1,
                ])->save();

                DB::table('privacy_deletion_ledger')
                    ->insertOrIgnore([
                        'entity_type' => 'Inquiry',
                        'entity_id' => $inquiry->id,
                        'category' => 'INQUIRY_PII',
                        'deleted_at' => $now,
                    ]);

                $this->audit->record(
                    action: 'privacy.inquiry_anonymized',
                    entity: $inquiry,
                    changes: [
                        'category' => 'INQUIRY_PII',
                        'anonymized' => true,
                    ],
                    actor: null,
                );

                return true;
            },
            attempts: 3,
        );
    }

    private function deletePrivateCv(
        CareerApplication $application,
    ): void {
        $path = $application->cv_path;

        if ($path === null || trim($path) === '') {
            return;
        }

        if ($application->cv_disk !== 'private') {
            throw new RuntimeException(
                'Refusing to delete a CV from a non-private disk.',
            );
        }

        $path = str_replace('\\', '/', $path);

        if (
            str_starts_with($path, '/')
            || preg_match(
                '#(^|/)\.\.(/|$)#',
                $path,
            ) === 1
        ) {
            throw new RuntimeException(
                'Stored CV path is invalid.',
            );
        }

        $disk = Storage::disk('private');

        if (
            $disk->exists($path)
            && ! $disk->delete($path)
        ) {
            throw new RuntimeException(
                'Private CV deletion failed.',
            );
        }
    }

    private function auditLogsToScrubQuery()
    {
        return DB::table('audit_logs')
            ->where(
                'created_at',
                '<',
                now()->subDays(90),
            )
            ->where(function ($query): void {
                $query
                    ->whereNotNull('ip')
                    ->orWhereNotNull('user_agent');
            });
    }

    private function auditLogsToDeleteQuery()
    {
        return DB::table('audit_logs')
            ->where(
                'created_at',
                '<',
                now()->subMonthsNoOverflow(12),
            );
    }
}
