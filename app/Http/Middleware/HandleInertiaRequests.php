<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Admin\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'admin-root';

    public function version(
        Request $request,
    ): ?string {
        return parent::version($request);
    }

    public function share(
        Request $request,
    ): array {
        /** @var User|null $user */
        $user = $request->user();

        return [
            ...parent::share($request),

            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
            ],

            'auth' => [
                'user' => $user === null
                    ? null
                    : [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],

                'permissions' => $user?->effectivePermissions()
                    ?? [],

                'is_super_admin' => $user?->isSuperAdmin()
                    ?? false,
            ],

            'navigation' => AdminNavigation::for(
                $user,
            ),

            'flash' => [
                'status' => fn () => $request
                    ->session()
                    ->get('status'),

                'success' => fn () => $request
                    ->session()
                    ->get('success'),
            ],
        ];
    }
}
