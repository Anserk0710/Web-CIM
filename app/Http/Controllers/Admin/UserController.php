<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUserProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
            ->withQueryString();

        return Inertia::render(
            'Admin/Users/Index',
            [
                'users' => $users,

                'roles' => Role::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'can' => [
                    'create' => request()
                        ->user()
                        ->hasPermission(
                            'user.create',
                        ),

                    'update' => request()
                        ->user()
                        ->hasPermission(
                            'user.update',
                        ),

                    'disable' => request()
                        ->user()
                        ->hasPermission(
                            'user.disable',
                        ),

                    'assignRoles' => request()
                        ->user()
                        ->hasPermission(
                            'user.assign_roles',
                        ),
                ],
            ],
        );
    }

    public function store(
        UserRequest $request,
        CreateUser $action,
    ): RedirectResponse {
        Gate::authorize(
            'user.create',
        );

        $action->handle(
            actor: $request->user(),
            name: $request->string(
                'name',
            )->toString(),
            email: $request->string(
                'email',
            )->toString(),
        );

        return back()->with(
            'success',
            'Pengguna baru berhasil ditambahkan dan email pengaturan kata sandi telah dikirimkan.',
        );
    }

    public function update(
        UserRequest $request,
        User $user,
        UpdateUserProfile $action,
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $user,
        );

        $action->handle(
            actor: $request->user(),
            target: $user,

            name: $request->string(
                'name',
            )->toString(),

            email: $request->string(
                'email',
            )->toString(),

            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Data pengguna berhasil diperbarui.',
        );
    }
}
