<?php

namespace App\Support\Admin;

use App\Models\User;

class AdminNavigation
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function for(
        ?User $user,
    ): array {
        if ($user === null) {
            return [];
        }

        $items = [
            [
                'label' => 'Dashboard',
                'href' => '/admin',
                'ability' => 'access-admin',
            ],

            [
                'label' => 'Products',
                'href' => '/admin/products',
                'ability' => 'product.read',
            ],

            [
                'label' => 'Taxonomies',
                'href' => '/admin/taxonomies',
                'ability' => 'taxonomy.read',
            ],

            [
                'label' => 'Media',
                'href' => '/admin/media',
                'ability' => 'media.read',
            ],

            [
                'label' => 'Users',
                'href' => '/admin/users',
                'ability' => 'user.read',
            ],

            [
                'label' => 'Roles',
                'href' => '/admin/roles',
                'ability' => 'role.read',
            ],
        ];

        return collect($items)
            ->filter(
                fn (array $item): bool => $item['ability']
                    === 'access-admin'
                    ? $user->hasAnyPermission()
                    : $user->hasPermission(
                        $item['ability'],
                    ),
            )
            ->map(
                fn (array $item): array => [
                    'label' => $item['label'],
                    'href' => $item['href'],
                ],
            )
            ->values()
            ->all();
    }
}
