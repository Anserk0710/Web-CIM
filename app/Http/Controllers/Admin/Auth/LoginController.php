<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\AuditResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render(
            'Admin/Auth/Login',
        );
    }

    public function store(
        LoginRequest $request,
        AuditService $audit,
    ): RedirectResponse {
        $key = $this->rateLimitKey(
            $request->string('email')->toString(),
            (string) $request->ip(),
        );

        if (
            RateLimiter::tooManyAttempts(
                $key,
                5,
            )
        ) {
            $seconds = RateLimiter::availableIn(
                $key,
            );

            throw ValidationException::withMessages([
                'email' => "Login gagal. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials = [
            'email' => $request->string(
                'email',
            )->toString(),

            'password' => $request->string(
                'password',
            )->toString(),

            'is_active' => true,
        ];

        if (
            ! Auth::attempt(
                $credentials,
                $request->boolean(
                    'remember',
                ),
            )
        ) {
            RateLimiter::hit($key, 60);

            $audit->record(
                action: 'auth.login_failed',
                result: AuditResult::Denied,
                changes: [
                    'email_hash' => hash(
                        'sha256',
                        $credentials['email'],
                    ),
                ],
            );

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        try {
            $audit->record(
                action: 'auth.login',
                entity: $user,
                actor: $user,
            );
        } catch (Throwable $exception) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw $exception;
        }

        return redirect()->intended(
            route('admin.dashboard'),
        );
    }

    public function destroy(
        Request $request,
        AuditService $audit,
    ): RedirectResponse {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null) {
            $audit->record(
                action: 'auth.logout',
                entity: $user,
                actor: $user,
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function rateLimitKey(
        string $email,
        string $ip,
    ): string {
        return 'login:'.hash(
            'sha256',
            $email.'|'.$ip,
        );
    }
}
