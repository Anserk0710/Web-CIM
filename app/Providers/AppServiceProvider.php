<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\Rbac\PermissionRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(
            User::class,
            UserPolicy::class,
        );

        Gate::policy(
            Role::class,
            RolePolicy::class,
        );

        foreach (
            PermissionRegistry::all() as $permission
        ) {
            Gate::define(
                $permission,
                fn (User $user): bool => $user
                    ->hasPermission($permission),
            );
        }

        Gate::define(
            'access-admin',
            fn (User $user): bool => $user
                ->hasAnyPermission(),
        );
    }
}
