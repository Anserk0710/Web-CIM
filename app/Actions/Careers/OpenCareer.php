<?php

namespace App\Actions\Careers;

use App\Enums\CareerStatus;
use App\Models\Career;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpenCareer
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
                    === CareerStatus::Archived
                ) {
                    throw ValidationException::withMessages([
                        'career' => 'Archived Career tidak dapat dibuka.',
                    ]);
                }

                $locked->forceFill([
                    'status' => CareerStatus::Open,

                    'opens_at' => $locked->opens_at
                        ?? now(),

                    'closed_at' => null,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'career.opened',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
