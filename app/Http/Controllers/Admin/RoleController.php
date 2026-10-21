<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
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

        return Inertia::render(
            'Admin/Roles/Index',
            [
                'roles' => Role::query()
                    ->with(
                        'permissions:id,name',
                    )
                    ->orderBy('name')
                    ->get(),

                'permissions' => Permission::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'canManage' => request()
                    ->user()
                    ->hasPermission(
                        'role.manage',
                    ),
            ],
        );
    }

    public function store(
        RoleRequest $request,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize(
            'role.manage',
        );

        $role = Role::query()->create([
            'name' => trim(
                $request->string(
                    'name',
                )->toString(),
            ),

            'description' => $request->string(
                'description',
            )->toString()
                ?: null,

            'is_system' => false,
            'lock_version' => 1,
        ]);

        $audit->record(
            action: 'role.created',
            entity: $role,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Role dibuat.',
        );
    }

    public function update(
        RoleRequest $request,
        Role $role,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize(
            'manage',
            $role,
        );

        if ($role->is_system) {
            throw new AuthorizationException(
                'System role semantics cannot be edited.',
            );
        }

        DB::transaction(
            function () use ($request, $role, $audit): void {
                $locked = Role::query()
                    ->whereKey($role->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $request->integer(
                        'lock_version',
                    )
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Role telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'name' => trim(
                        $request->string(
                            'name',
                        )->toString(),
                    ),

                    'description' => $request->string(
                        'description',
                    )->toString()
                        ?: null,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $audit->record(
                    action: 'role.updated',
                    entity: $locked,
                    actor: $request->user(),
                );
            },
            attempts: 3,
        );

        return back()->with(
            'success',
            'Role diperbarui.',
        );
    }
}
