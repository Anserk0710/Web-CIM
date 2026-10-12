<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('role.read');
    }

    public function view(
        User $actor,
        Role $role,
    ): bool {
        return $actor->hasPermission('role.read');
    }

    public function manage(
        User $actor,
        Role $role,
    ): bool {
        return $actor->hasPermission(
            'role.manage',
        );
    }
}
