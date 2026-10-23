<?php // path: app/Actions/CareerApplications/MarkCareerApplicationHandover.php

namespace App\Actions\CareerApplications;

use App\Enums\ApplicationStatus;
use App\Models\CareerApplication;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MarkCareerApplicationHandover
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        CareerApplication $application,
        int $expectedLockVersion,
    ): CareerApplication {
        Gate::forUser($actor)->authorize(
            'application.update',
        );

        return DB::transaction(
            function () use (
                $actor,
                $application,
                $expectedLockVersion,
            ): CareerApplication {
                $locked = CareerApplication::query()
                    ->whereKey($application->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' =>
                            'Application telah berubah. Muat ulang halaman sebelum mencatat handover.',
                    ]);
                }

                if ($locked->anonymized_at !== null) {
                    throw ValidationException::withMessages([
                        'application' =>
                            'Application yang sudah dianonimkan tidak dapat diproses.',
                    ]);
                }

                if (
                    $locked->status
                    !== ApplicationStatus::Hired
                ) {
                    throw ValidationException::withMessages([
                        'application' =>
                            'Handover hanya tersedia untuk kandidat berstatus HIRED.',
                    ]);
                }

                if ($locked->hr_handover_at !== null) {
                    throw ValidationException::withMessages([
                        'application' =>
                            'Handover HR sudah tercatat.',
                    ]);
                }

                $handoverAt = now();

                $locked->forceFill([
                    'hr_handover_at' => $handoverAt,

                    'retention_due_at' => $handoverAt
                        ->copy()
                        ->addDays(30),

                    'lock_version' =>
                        $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'application.hr_handover_recorded',
                    entity: $locked,
                    changes: [
                        'status' => ApplicationStatus::Hired->value,
                        'retention_due_at' => $locked
                            ->retention_due_at
                            ?->toIso8601String(),
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
