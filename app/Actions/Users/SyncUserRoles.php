<?php

namespace App\Actions\Users;

use App\Models\AccessGuard;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\Rbac\PermissionDelegationGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SyncUserRoles
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly PermissionDelegationGuard $delegation,
    ) {}

    /**
     * @param  array<int, int>  $roleIds
     */
    public function handle(
        User $actor,
        User $target,
        array $roleIds,
        int $expectedLockVersion,
    ): User {
        Gate::forUser($actor)->authorize(
            'assignRoles',
            $target,
        );

        return DB::transaction(
            function () use ($actor, $target, $roleIds, $expectedLockVersion): User {
                AccessGuard::query()
                    ->whereKey(1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail($target->id);

                if (
                    $lockedUser->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Data user telah berubah. Muat ulang halaman.',
                    ]);
                }

                $roles = Role::query()
                    ->whereIn('id', $roleIds)
                    ->lockForUpdate()
                    ->get();

                if (
                    $roles->count() !== count(
                        array_unique($roleIds),
                    )
                ) {
                    throw ValidationException::withMessages([
                        'role_ids' => 'Satu atau lebih role tidak valid.',
                    ]);
                }

                $before = $lockedUser->roles()
                    ->pluck('roles.name')
                    ->sort()
                    ->values()
                    ->all();

                $after = $roles
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all();

                $hadSuperAdmin = in_array(
                    Role::SUPER_ADMIN,
                    $before,
                    true,
                );

                $willHaveSuperAdmin = in_array(
                    Role::SUPER_ADMIN,
                    $after,
                    true,
                );

                if (
                    $hadSuperAdmin !== $willHaveSuperAdmin
                    && ! $actor->isSuperAdmin()
                ) {
                    throw new AuthorizationException(
                        'Role Super Admin hanya dapat diberikan atau dicabut oleh Super Admin aktif.',
                    );
                }

                $permissionNames = $roles
                    ->load('permissions:id,name')
                    ->flatMap(
                        fn (Role $role) => $role
                            ->permissions
                            ->pluck('name'),
                    )
                    ->unique()
                    ->values()
                    ->all();

                $this->delegation->assertCanDelegate(
                    $actor,
                    $permissionNames,
                );

                if (
                    $hadSuperAdmin
                    && ! $willHaveSuperAdmin
                    && $lockedUser->is_active
                    && $this->activeSuperAdminCount() <= 1
                ) {
                    throw ValidationException::withMessages([
                        'role_ids' => 'Role Super Admin aktif terakhir tidak dapat dicabut.',
                    ]);
                }

                $lockedUser
                    ->roles()
                    ->sync($roles->modelKeys());

                $lockedUser->forceFill([
                    'lock_version' => $lockedUser->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'user.roles_updated',
                    entity: $lockedUser,
                    changes: [
                        'roles' => [
                            'before' => $before,
                            'after' => $after,
                        ],
                    ],
                    actor: $actor,
                );

                return $lockedUser->refresh();
            },
            attempts: 3,
        );
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas(
                'roles',
                fn ($query) => $query->where(
                    'roles.name',
                    Role::SUPER_ADMIN,
                ),
            )
            ->count();
    }
}
