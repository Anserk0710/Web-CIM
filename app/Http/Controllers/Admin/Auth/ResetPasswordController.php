<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Auth\SessionRevoker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function create(
        Request $request,
        string $token,
    ): Response {
        return Inertia::render(
            'Admin/Auth/ResetPassword',
            [
                'token' => $token,
                'email' => (string) $request->query(
                    'email',
                    '',
                ),
            ],
        );
    }

    public function store(
        ResetPasswordRequest $request,
        AuditService $audit,
        SessionRevoker $sessions,
    ): RedirectResponse {
        $email = $request->string(
            'email',
        )->toString();

        $activeUserExists = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->exists();

        if (! $activeUserExists) {
            throw ValidationException::withMessages([
                'email' => 'Tautan atur ulang kata sandi tidak valid atau telah kedaluwarsa.',
            ]);
        }

        $status = Password::broker()->reset(
            [
                'email' => $email,

                'password' => $request->string(
                    'password',
                )->toString(),

                'password_confirmation' => $request->string(
                    'password_confirmation',
                )->toString(),

                'token' => $request->string(
                    'token',
                )->toString(),
            ],
            function (User $user, string $password) use ($audit, $sessions): void {
                DB::transaction(
                    function () use ($user, $password, $audit, $sessions): void {
                        $locked = User::query()
                            ->whereKey($user->id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        if (! $locked->is_active) {
                            throw ValidationException::withMessages([
                                'email' => 'Tautan atur ulang kata sandi tidak valid atau telah kedaluwarsa.',
                            ]);
                        }

                        $locked->forceFill([
                            'password' => $password,
                            'remember_token' => Str::random(
                                60,
                            ),
                            'lock_version' => $locked->lock_version + 1,
                        ])->save();

                        $sessions->revokeAll(
                            $locked,
                        );

                        $audit->record(
                            action: 'auth.password_reset',
                            entity: $locked,
                            actor: $locked,
                        );
                    },
                    attempts: 3,
                );
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'Tautan atur ulang kata sandi tidak valid atau telah kedaluwarsa.',
            ]);
        }

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru Anda.',
            );
    }
}
