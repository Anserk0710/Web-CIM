<?php

namespace App\Policies;

use App\Models\EcatalogEntry;
use App\Models\User;

class EcatalogEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'ecatalog.read',
        );
    }

    public function view(
        User $user,
        EcatalogEntry $entry,
    ): bool {
        return $user->hasPermission(
            'ecatalog.read',
        );
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(
            'ecatalog.create',
        );
    }

    public function update(
        User $user,
        EcatalogEntry $entry,
    ): bool {
        return $user->hasPermission(
            'ecatalog.update',
        );
    }

    public function delete(
        User $user,
        EcatalogEntry $entry,
    ): bool {
        return $user->hasPermission(
            'ecatalog.delete',
        );
    }

    public function publish(
        User $user,
        EcatalogEntry $entry,
    ): bool {
        return $user->hasPermission(
            'ecatalog.publish',
        );
    }

    public function archive(
        User $user,
        EcatalogEntry $entry,
    ): bool {
        return $user->hasPermission(
            'ecatalog.archive',
        );
    }
}
