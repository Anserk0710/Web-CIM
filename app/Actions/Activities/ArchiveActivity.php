<?php

namespace App\Actions\Activities;

use App\Enums\ContentStatus;
use App\Models\Activity;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchiveActivity
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Activity $activity,
        int $expectedLockVersion,
    ): Activity {
        return DB::transaction(
            function () use ($actor, $activity, $expectedLockVersion): Activity {
                $locked = Activity::query()
                    ->whereKey($activity->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Activity telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'status' => ContentStatus::Archived,

                    'published_at' => null,
                    'archived_at' => now(),
                    'updated_by' => $actor->id,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'activity.archived',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
