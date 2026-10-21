<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'activity.read',
        );
    }

    public function view(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.read',
        );
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(
            'activity.create',
        );
    }

    public function update(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.update',
        );
    }

    public function delete(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.delete',
        );
    }

    public function publish(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.publish',
        );
    }

    public function archive(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.archive',
        );
    }

    public function preview(
        User $user,
        Activity $activity,
    ): bool {
        return $user->hasPermission(
            'activity.read',
        );
    }
}
