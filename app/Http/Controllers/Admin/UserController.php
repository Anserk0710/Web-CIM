<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(
            'viewAny',
            User::class,
        );

        $users = User::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'lock_version' => $user->lock_version,

                    'roles' => $user->roles
                        ->map(
                            fn ($role): array => [
                                'id' => $role->id,
                                'name' => $role->name,
                            ],
                        )
                        ->values(),
                ],
            );

        return Inertia::render(
            'Admin/Users/Index',
            [
                'users' => $users,
            ],
        );
    }
}
