<?php

namespace App\Policies;

use App\Models\Career;
use App\Models\User;

class CareerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'career.read',
        );
    }

    public function view(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.read',
        );
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(
            'career.create',
        );
    }

    public function update(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.update',
        );
    }

    public function delete(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.delete',
        );
    }

    public function publish(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.publish',
        );
    }

    public function close(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.close',
        );
    }

    public function archive(
        User $user,
        Career $career,
    ): bool {
        return $user->hasPermission(
            'career.archive',
        );
    }
}
