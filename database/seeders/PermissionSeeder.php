<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\Rbac\PermissionRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            PermissionRegistry::all() as $permission
        ) {
            Permission::query()->updateOrCreate(
                [
                    'name' => $permission,
                ],
                [
                    'description' => Str::headline(
                        str_replace(
                            '.',
                            ' ',
                            $permission,
                        ),
                    ),
                ],
            );
        }
    }
}
