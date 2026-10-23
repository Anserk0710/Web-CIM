<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\SetUserActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserActiveRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserAccessController extends Controller
{
    public function update(
        UpdateUserActiveRequest $request,
        User $user,
        SetUserActiveStatus $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),
            target: $user,
            isActive: $request->boolean(
                'is_active',
            ),
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Status keaktifan pengguna berhasil diperbarui.',
        );
    }
}
