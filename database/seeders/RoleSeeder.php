<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Rbac\PermissionRegistry;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            PermissionRegistry::roleTemplates() as $roleName => $permissions
        ) {
            $role = Role::query()->updateOrCreate(
                [
                    'name' => $roleName,
                ],
                [
                    'description' => $this->description(
                        $roleName,
                    ),

                    'is_system' => $roleName
                        === Role::SUPER_ADMIN,
                ],
            );

            $permissionIds = Permission::query()
                ->whereIn(
                    'name',
                    $permissions,
                )
                ->pluck('id');

            $role->permissions()->sync(
                $permissionIds,
            );
        }
    }

    private function description(
        string $role,
    ): string {
        return match ($role) {
            'Super Admin' => 'Full system administration.',
            'Admin' => 'General content administration.',
            'Editor' => 'Draft content editing.',
            'Product' => 'Product and catalog management.',
            'HR' => 'Career and applicant workflow.',
            'Read Only' => 'Read-only public-content administration.',
            default => 'Initial role template.',
        };
    }
}
