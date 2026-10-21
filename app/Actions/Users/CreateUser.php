<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUser
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        string $name,
        string $email,
    ): User {
        return DB::transaction(
            function () use ($actor, $name, $email): User {
                $user = User::query()->create([
                    'name' => trim($name),

                    'email' => Str::lower(
                        trim($email),
                    ),

                    'password' => Str::password(
                        length: 40,
                    ),

                    'is_active' => true,
                    'lock_version' => 1,
                ]);

                $this->audit->record(
                    action: 'user.created',
                    entity: $user,
                    actor: $actor,
                );

                DB::afterCommit(
                    function () use ($user): void {
                        Password::broker()
                            ->sendResetLink([
                                'email' => $user->email,
                            ]);
                    },
                );

                return $user;
            },
            attempts: 3,
        );
    }
}
