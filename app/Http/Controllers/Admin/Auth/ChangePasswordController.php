<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Auth\SessionRevoker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render(
            'Admin/Auth/ChangePassword',
        );
    }

    public function update(
        ChangePasswordRequest $request,
        AuditService $audit,
        SessionRevoker $sessions,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        if (
            ! Hash::check(
                $request->string(
                    'current_password',
                )->toString(),
                $user->password,
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        DB::transaction(
            function () use ($request, $user, $audit, $sessions): void {
                $locked = User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->forceFill([
                    'password' => $request->string(
                        'password',
                    )->toString(),

                    'remember_token' => Str::random(
                        60,
                    ),

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $sessions->revokeAllExcept(
                    $locked,
                    $request->session()->getId(),
                );

                $audit->record(
                    action: 'auth.password_changed',
                    entity: $locked,
                    actor: $locked,
                );
            },
            attempts: 3,
        );

        return back()->with(
            'success',
            'Kata sandi Anda berhasil diperbarui.',
        );
    }
}
