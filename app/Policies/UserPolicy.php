<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('user.read');
    }

    public function view(
        User $actor,
        User $target,
    ): bool {
        return $actor->hasPermission('user.read');
    }

    public function update(
        User $actor,
        User $target,
    ): bool {
        return $actor->hasPermission(
            'user.update',
        );
    }

    public function disable(
        User $actor,
        User $target,
    ): bool {
        return $actor->hasPermission(
            'user.disable',
        );
    }

    public function assignRoles(
        User $actor,
        User $target,
    ): bool {
        return $actor->hasPermission(
            'user.assign_roles',
        );
    }
}
