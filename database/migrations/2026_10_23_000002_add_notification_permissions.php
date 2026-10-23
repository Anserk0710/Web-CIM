<?php // path: database/migrations/2026_10_23_000002_add_notification_permissions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insertOrIgnore([
            [
                'name' => 'notification.read',
                'description' => 'Notification Read',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'notification.retry',
                'description' => 'Notification Retry',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $readId = DB::table('permissions')
            ->where('name', 'notification.read')
            ->value('id');

        $retryId = DB::table('permissions')
            ->where('name', 'notification.retry')
            ->value('id');

        $superAdminId = DB::table('roles')
            ->where('name', 'Super Admin')
            ->value('id');

        $adminId = DB::table('roles')
            ->where('name', 'Admin')
            ->value('id');

        if ($superAdminId !== null) {
            DB::table('role_permission')->insertOrIgnore([
                [
                    'role_id' => $superAdminId,
                    'permission_id' => $readId,
                ],
                [
                    'role_id' => $superAdminId,
                    'permission_id' => $retryId,
                ],
            ]);
        }

        if ($adminId !== null) {
            DB::table('role_permission')->insertOrIgnore([
                [
                    'role_id' => $adminId,
                    'permission_id' => $readId,
                ],
            ]);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', [
                'notification.read',
                'notification.retry',
            ])
            ->pluck('id');

        DB::table('role_permission')
            ->whereIn('permission_id', $ids)
            ->delete();

        DB::table('permissions')
            ->whereIn('name', [
                'notification.read',
                'notification.retry',
            ])
            ->delete();
    }
};
