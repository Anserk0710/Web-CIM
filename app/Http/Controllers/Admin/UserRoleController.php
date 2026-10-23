<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\SyncUserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncUserRolesRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserRoleController extends Controller
{
    public function update(
        SyncUserRolesRequest $request,
        User $user,
        SyncUserRoles $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),
            target: $user,
            roleIds: $request->input(
                'role_ids',
                [],
            ),
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Peran pengguna berhasil diperbarui.',
        );
    }
}
