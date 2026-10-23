<?php

namespace App\Actions\CareerApplications;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationStatusEvent;
use App\Models\CareerApplication;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Career\ApplicationStatusWorkflow;
use App\Services\Privacy\ApplicationRetentionSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChangeCareerApplicationStatus
{
    public function __construct(
        private readonly ApplicationStatusWorkflow $workflow,
        private readonly AuditService $audit,
        private readonly ApplicationRetentionSchedule $retentionSchedule,
    ) {}

    public function handle(
        User $actor,
        CareerApplication $application,
        ApplicationStatus $targetStatus,
        ?string $reason,
        int $expectedLockVersion,
    ): CareerApplication {
        Gate::forUser($actor)->authorize(
            'update',
            $application,
        );

        return DB::transaction(
            function () use (
                $actor,
                $application,
                $targetStatus,
                $reason,
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
                        'lock_version' => 'Application telah berubah. Muat ulang halaman.',
                    ]);
                }

                if ($locked->isAnonymized()) {
                    throw ValidationException::withMessages([
                        'application' => 'Application yang telah dianonimkan tidak dapat diubah.',
                    ]);
                }

                $fromStatus = $locked->status;

                if (
                    ! $this->workflow
                        ->canTransition(
                            $fromStatus,
                            $targetStatus,
                        )
                ) {
                    throw ValidationException::withMessages([
                        'target_status' => 'Transisi status tidak diizinkan.',
                    ]);
                }

                $reason = trim(
                    (string) $reason,
                );

                if (
                    $this->workflow
                        ->requiresReason(
                            $fromStatus,
                            $targetStatus,
                        )
                    && $reason === ''
                ) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan wajib diisi untuk transisi ini.',
                    ]);
                }

                $retentionFields = $this->retentionSchedule->fieldsForTransition(
                    $locked->status,
                    $targetStatus,
                    now(),
                );

                $locked->forceFill([
                    'status' => $targetStatus,
                    'lock_version' => $locked->lock_version + 1,
                    ...$retentionFields,
                ])->save();

                ApplicationStatusEvent::query()
                    ->create([
                        'application_id' => $locked->id,

                        'actor_id' => $actor->id,

                        'from_status' => $fromStatus,

                        'to_status' => $targetStatus,

                        'reason' => $reason === ''
                                ? null
                                : $reason,

                        'created_at' => now(),
                    ]);

                $this->audit->record(
                    action: 'application.status_changed',
                    entity: $locked,
                    changes: [
                        'status' => [
                            'before' => $fromStatus->value,

                            'after' => $targetStatus->value,
                        ],
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
