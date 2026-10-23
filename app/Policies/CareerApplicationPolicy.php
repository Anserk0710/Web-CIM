<?php

namespace App\Policies;

use App\Models\CareerApplication;
use App\Models\User;

class CareerApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'application.read',
        );
    }

    public function view(
        User $user,
        CareerApplication $application,
    ): bool {
        return $user->hasPermission(
            'application.read',
        );
    }

    public function update(
        User $user,
        CareerApplication $application,
    ): bool {
        return $user->hasPermission(
            'application.update',
        );
    }

    public function download(
        User $user,
        CareerApplication $application,
    ): bool {
        return $user->hasPermission(
            'application.read',
        )
            && $user->hasPermission(
                'application.download',
            );
    }

    public function delete(
        User $user,
        CareerApplication $application,
    ): bool {
        return $user->hasPermission(
            'application.delete',
        );
    }

    public function export(User $user): bool
    {
        return $user->hasPermission(
            'application.read',
        )
            && $user->hasPermission(
                'application.export',
            );
    }
}
