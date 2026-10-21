<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateUserProfile
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        User $target,
        string $name,
        string $email,
        int $expectedLockVersion,
    ): User {
        return DB::transaction(
            function () use ($actor, $target, $name, $email, $expectedLockVersion): User {
                $locked = User::query()
                    ->whereKey($target->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'User telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'name' => trim($name),

                    'email' => Str::lower(
                        trim($email),
                    ),

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'user.updated',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
