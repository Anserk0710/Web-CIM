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
                'label' => 'Dasbor',
                'href' => '/admin',
                'ability' => 'access-admin',
            ],

            [
                'label' => 'Produk',
                'href' => '/admin/products',
                'ability' => 'product.read',
            ],

            [
                'label' => 'E-Katalog',
                'href' => '/admin/ecatalog',
                'ability' => 'ecatalog.read',
            ],

            [
                'label' => 'Berita & Kegiatan',
                'href' => '/admin/activities',
                'ability' => 'activity.read',
            ],

            [
                'label' => 'Karir & Lowongan',
                'href' => '/admin/careers',
                'ability' => 'career.read',
            ],

            [
                'label' => 'Lamaran Masuk',
                'href' => '/admin/applications',
                'ability' => 'application.read',
            ],

            [
                'label' => 'Pesan Masuk',
                'href' => '/admin/inquiries',
                'ability' => 'inquiry.read',
            ],

            [
                'label' => 'Kategori Produk',
                'href' => '/admin/taxonomies',
                'ability' => 'taxonomy.read',
            ],

            [
                'label' => 'Galeri Media',
                'href' => '/admin/media',
                'ability' => 'media.read',
            ],

            [
                'label' => 'Pengaturan Situs',
                'href' => '/admin/settings',
                'ability' => 'settings.read',
            ],

            [
                'label' => 'Pengguna',
                'href' => '/admin/users',
                'ability' => 'user.read',
            ],

            [
                'label' => 'Peran & Hak Akses',
                'href' => '/admin/roles',
                'ability' => 'role.read',
            ],

            [
                'label' => 'Catatan Aktivitas',
                'href' => '/admin/audit',
                'ability' => 'audit.read',
            ],

            [
                'label' => 'Notifikasi Operasional',
                'href' => '/admin/operations/notifications',
                'ability' => 'notification.read',
            ],
        ];

        return collect($items)
            ->filter(
                fn (array $item): bool => $item['ability']
                    === 'access-admin'
                    ? $user
                        ->hasAnyPermission()
                    : $user
                        ->hasPermission(
                            $item[
                                'ability'
                            ],
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
