<?php

namespace App\Actions\Careers;

use App\Enums\CareerStatus;
use App\Models\Career;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseCareer
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Career $career,
        int $expectedLockVersion,
    ): Career {
        return DB::transaction(
            function () use ($actor, $career, $expectedLockVersion): Career {
                $locked = Career::query()
                    ->whereKey($career->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Career telah berubah.',
                    ]);
                }

                if (
                    $locked->status
                    !== CareerStatus::Open
                ) {
                    throw ValidationException::withMessages([
                        'career' => 'Only OPEN Career can be closed.',
                    ]);
                }

                $locked->forceFill([
                    'status' => CareerStatus::Closed,

                    'closed_at' => now(),

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'career.closed',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
