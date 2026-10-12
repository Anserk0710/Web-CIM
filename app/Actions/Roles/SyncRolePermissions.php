<?php

namespace App\Actions\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\Rbac\PermissionDelegationGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SyncRolePermissions
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly PermissionDelegationGuard $delegation,
    ) {}

    /**
     * @param  array<int, int>  $permissionIds
     */
    public function handle(
        User $actor,
        Role $role,
        array $permissionIds,
        int $expectedLockVersion,
    ): Role {
        Gate::forUser($actor)->authorize(
            'manage',
            $role,
        );

        return DB::transaction(
            function () use ($actor, $role, $permissionIds, $expectedLockVersion): Role {
                $lockedRole = Role::query()
                    ->lockForUpdate()
                    ->findOrFail($role->id);

                if (
                    $lockedRole->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Data role telah berubah. Muat ulang halaman.',
                    ]);
                }

                if (
                    $lockedRole->isSuperAdminRole()
                ) {
                    throw new AuthorizationException(
                        'Semantik role Super Admin tidak dapat diubah.',
                    );
                }

                $permissions = Permission::query()
                    ->whereIn(
                        'id',
                        $permissionIds,
                    )
                    ->get();

                if (
                    $permissions->count()
                    !== count(
                        array_unique(
                            $permissionIds,
                        ),
                    )
                ) {
                    throw ValidationException::withMessages([
                        'permission_ids' => 'Satu atau lebih permission tidak valid.',
                    ]);
                }

                $this->delegation->assertCanDelegate(
                    $actor,
                    $permissions
                        ->pluck('name')
                        ->all(),
                );

                $before = $lockedRole
                    ->permissions()
                    ->pluck('permissions.name')
                    ->sort()
                    ->values()
                    ->all();

                $lockedRole->permissions()->sync(
                    $permissions->modelKeys(),
                );

                $lockedRole->forceFill([
                    'lock_version' => $lockedRole->lock_version + 1,
                ])->save();

                $after = $permissions
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all();

                $this->audit->record(
                    action: 'role.permissions_updated',
                    entity: $lockedRole,
                    changes: [
                        'permissions' => [
                            'before' => $before,
                            'after' => $after,
                        ],
                    ],
                    actor: $actor,
                );

                return $lockedRole->refresh();
            },
            attempts: 3,
        );
    }
}
