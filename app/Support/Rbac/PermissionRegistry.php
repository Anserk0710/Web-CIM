<?php

namespace App\Support\Rbac;

final class PermissionRegistry
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'product.read',
            'product.create',
            'product.update',
            'product.delete',
            'product.publish',
            'product.archive',

            'ecatalog.read',
            'ecatalog.create',
            'ecatalog.update',
            'ecatalog.delete',
            'ecatalog.publish',
            'ecatalog.archive',

            'activity.read',
            'activity.create',
            'activity.update',
            'activity.delete',
            'activity.publish',
            'activity.archive',

            'career.read',
            'career.create',
            'career.update',
            'career.delete',
            'career.publish',
            'career.close',
            'career.archive',

            'taxonomy.read',
            'taxonomy.manage',

            'application.read',
            'application.update',
            'application.delete',
            'application.download',
            'application.export',

            'inquiry.read',
            'inquiry.update',
            'inquiry.delete',
            'inquiry.export',

            'media.read',
            'media.upload',
            'media.update',
            'media.delete',

            'page.read',
            'page.update',

            'settings.read',
            'settings.update',

            'user.read',
            'user.create',
            'user.update',
            'user.disable',
            'user.assign_roles',

            'role.read',
            'role.manage',

            'audit.read',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function roleTemplates(): array
    {
        return [
            'Super Admin' => self::all(),

            'Admin' => [
                'product.read',
                'product.create',
                'product.update',
                'product.publish',
                'product.archive',

                'ecatalog.read',
                'ecatalog.create',
                'ecatalog.update',
                'ecatalog.publish',
                'ecatalog.archive',

                'activity.read',
                'activity.create',
                'activity.update',
                'activity.publish',
                'activity.archive',

                'taxonomy.read',
                'taxonomy.manage',

                'media.read',
                'media.upload',
                'media.update',

                'page.read',
                'page.update',

                'settings.read',
                'settings.update',

                'inquiry.read',
                'inquiry.update',
            ],

            'Editor' => [
                'product.read',
                'product.create',
                'product.update',

                'ecatalog.read',
                'ecatalog.create',
                'ecatalog.update',

                'activity.read',
                'activity.create',
                'activity.update',

                'taxonomy.read',

                'media.read',
                'media.upload',
                'media.update',

                'page.read',
            ],

            'Product' => [
                'product.read',
                'product.create',
                'product.update',

                'ecatalog.read',
                'ecatalog.create',
                'ecatalog.update',

                'taxonomy.read',
                'taxonomy.manage',

                'media.read',
                'media.upload',
                'media.update',
            ],

            'HR' => [
                'career.read',
                'career.create',
                'career.update',
                'career.publish',
                'career.close',
                'career.archive',

                'application.read',
                'application.update',
                'application.download',
            ],

            'Read Only' => [
                'product.read',
                'ecatalog.read',
                'activity.read',
                'career.read',
                'taxonomy.read',
                'page.read',
            ],
        ];
    }

    public static function isSensitiveForDelegation(
        string $permission,
    ): bool {
        if (
            str_starts_with($permission, 'user.')
            || str_starts_with($permission, 'role.')
        ) {
            return true;
        }

        if ($permission === 'audit.read') {
            return true;
        }

        if (str_ends_with($permission, '.delete')) {
            return true;
        }

        return str_ends_with(
            $permission,
            '.export',
        );
    }
}
