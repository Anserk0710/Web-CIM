<?php

namespace App\Support\Rbac;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class PermissionDelegationGuard
{
    /**
     * @param  iterable<int, string>  $permissions
     */
    public function assertCanDelegate(
        User $actor,
        iterable $permissions,
    ): void {
        if ($actor->isSuperAdmin()) {
            return;
        }

        foreach ($permissions as $permission) {
            if (
                PermissionRegistry::isSensitiveForDelegation(
                    $permission,
                )
            ) {
                throw new AuthorizationException(
                    'Permission tersebut tidak dapat didelegasikan.',
                );
            }

            if (! $actor->hasPermission($permission)) {
                throw new AuthorizationException(
                    'Tidak dapat memberikan permission yang tidak dimiliki.',
                );
            }
        }
    }
}
