<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Roles\SyncRolePermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;

class RolePermissionController extends Controller
{
    public function update(
        SyncRolePermissionsRequest $request,
        Role $role,
        SyncRolePermissions $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),
            role: $role,
            permissionIds: $request->input(
                'permission_ids',
                [],
            ),
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Hak akses peran berhasil diperbarui.',
        );
    }
}
