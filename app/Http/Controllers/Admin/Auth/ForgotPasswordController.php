<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render(
            'Admin/Auth/ForgotPassword',
        );
    }

    public function store(
        ForgotPasswordRequest $request,
        AuditService $audit,
    ): RedirectResponse {
        $email = $request->string(
            'email',
        )->toString();

        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if ($user !== null) {
            Password::broker()->sendResetLink([
                'email' => $email,
            ]);

            $audit->record(
                action: 'auth.password_reset_requested',
                entity: $user,
            );
        }

        return back()->with(
            'status',
            'Jika alamat email terdaftar dan aktif di sistem, tautan atur ulang kata sandi telah dikirimkan ke email Anda.',
        );
    }
}
