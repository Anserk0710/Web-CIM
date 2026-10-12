<?php

namespace App\Actions\Users;

use App\Models\AccessGuard;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Auth\SessionRevoker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SetUserActiveStatus
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly SessionRevoker $sessions,
    ) {}

    public function handle(
        User $actor,
        User $target,
        bool $isActive,
        int $expectedLockVersion,
    ): User {
        Gate::forUser($actor)->authorize(
            'disable',
            $target,
        );

        return DB::transaction(
            function () use ($actor, $target, $isActive, $expectedLockVersion): User {
                AccessGuard::query()
                    ->whereKey(1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked = User::query()
                    ->lockForUpdate()
                    ->findOrFail($target->id);

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Data user telah berubah. Muat ulang halaman.',
                    ]);
                }

                if ($locked->is_active === $isActive) {
                    return $locked;
                }

                if (
                    ! $isActive
                    && $locked->is_active
                    && $locked->isSuperAdmin()
                    && $this->activeSuperAdminCount() <= 1
                ) {
                    throw ValidationException::withMessages([
                        'user' => 'Super Admin aktif terakhir tidak dapat dinonaktifkan.',
                    ]);
                }

                $before = $locked->is_active;

                $locked->forceFill([
                    'is_active' => $isActive,
                    'remember_token' => $isActive
                        ? $locked->remember_token
                        : null,
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                if (! $isActive) {
                    $this->sessions->revokeAll(
                        $locked,
                    );
                }

                $this->audit->record(
                    action: $isActive
                    ? 'user.activated'
                    : 'user.deactivated',
                    entity: $locked,
                    changes: [
                        'is_active' => [
                            'before' => $before,
                            'after' => $isActive,
                        ],
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
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
