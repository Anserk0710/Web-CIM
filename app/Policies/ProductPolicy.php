<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'product.read',
        );
    }

    public function view(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.read',
        );
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(
            'product.create',
        );
    }

    public function update(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.update',
        );
    }

    public function delete(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.delete',
        );
    }

    public function publish(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.publish',
        );
    }

    public function archive(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.archive',
        );
    }

    public function preview(
        User $user,
        Product $product,
    ): bool {
        return $user->hasPermission(
            'product.read',
        );
    }
}
