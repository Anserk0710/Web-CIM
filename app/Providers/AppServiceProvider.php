<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\EcatalogEntry;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\CareerApplicationPolicy;
use App\Policies\CareerPolicy;
use App\Policies\EcatalogEntryPolicy;
use App\Policies\InquiryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\Rbac\PermissionRegistry;
use App\View\Composers\PublicSiteLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        Gate::policy(
            Product::class,
            ProductPolicy::class,
        );

        Gate::policy(
            EcatalogEntry::class,
            EcatalogEntryPolicy::class,
        );

        Gate::policy(
            Activity::class,
            ActivityPolicy::class,
        );

        Gate::policy(
            Career::class,
            CareerPolicy::class,
        );

        Gate::policy(
            CareerApplication::class,
            CareerApplicationPolicy::class,
        );

        Gate::policy(
            Inquiry::class,
            InquiryPolicy::class,
        );

        foreach (
            PermissionRegistry::all() as $permission
        ) {
            Gate::define(
                $permission,

                fn (
                    User $user,
                ): bool => $user
                    ->hasPermission(
                        $permission,
                    ),
            );
        }

        Gate::define(
            'access-admin',

            fn (
                User $user,
            ): bool => $user
                ->hasAnyPermission(),
        );

        View::composer(
            [
                'layouts.public',
                'public.home',
                'public.company',
                'public.contact',
            ],
            PublicSiteLayoutComposer::class,
        );
    }
}
