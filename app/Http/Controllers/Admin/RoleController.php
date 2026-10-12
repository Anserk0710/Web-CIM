<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(
            'viewAny',
            Role::class,
        );

        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(
                fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_system' => $role->is_system,
                    'lock_version' => $role->lock_version,

                    'permissions' => $role
                        ->permissions
                        ->pluck('name')
                        ->sort()
                        ->values(),
                ],
            );

        return Inertia::render(
            'Admin/Roles/Index',
            [
                'roles' => $roles,
            ],
        );
    }
}
