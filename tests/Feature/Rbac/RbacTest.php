<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }

    public function test_permission_without_user_read_returns_403(): void
    {
        $role = $this->roleWithPermissions(
            'Product Reader',
            [
                'product.read',
            ],
        );

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_super_admin_is_allowed(): void
    {
        $user = $this->superAdmin();

        $this->withoutVite();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_permission_change_applies_on_next_request(): void
    {
        $role = $this->roleWithPermissions(
            'Temporary Reader',
            [
                'product.read',
            ],
        );

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();

        $permission = Permission::query()
            ->where('name', 'user.read')
            ->firstOrFail();

        $role->permissions()->attach(
            $permission,
        );

        $this->withoutVite();

        $this->get('/admin/users')
            ->assertOk();
    }

    public function test_role_manage_alone_cannot_assign_user_roles(): void
    {
        $role = $this->roleWithPermissions(
            'Role Manager Only',
            [
                'role.manage',
            ],
        );

        $actor = User::factory()->create();

        $actor->roles()->attach($role);

        $target = User::factory()->create();

        $targetRole = Role::query()
            ->where('name', 'Editor')
            ->firstOrFail();

        $this->actingAs($actor)
            ->put(
                "/admin/users/{$target->id}/roles",
                [
                    'role_ids' => [
                        $targetRole->id,
                    ],
                    'lock_version' => $target->lock_version,
                ],
            )
            ->assertForbidden();
    }

    public function test_user_cannot_delegate_permission_not_owned(): void
    {
        $actorRole = $this->roleWithPermissions(
            'Limited Delegator',
            [
                'user.assign_roles',
                'product.read',
            ],
        );

        $highRole = $this->roleWithPermissions(
            'Publisher',
            [
                'product.publish',
            ],
        );

        $actor = User::factory()->create();

        $actor->roles()->attach(
            $actorRole,
        );

        $target = User::factory()->create();

        $this->actingAs($actor)
            ->put(
                "/admin/users/{$target->id}/roles",
                [
                    'role_ids' => [
                        $highRole->id,
                    ],
                    'lock_version' => $target->lock_version,
                ],
            )
            ->assertForbidden();
    }

    public function test_last_super_admin_cannot_be_deactivated(): void
    {
        $actor = $this->superAdmin();

        $this->actingAs($actor)
            ->patch(
                "/admin/users/{$actor->id}/active",
                [
                    'is_active' => false,
                    'lock_version' => $actor->lock_version,
                ],
            )
            ->assertSessionHasErrors('user');

        $this->assertTrue(
            $actor->refresh()->is_active,
        );
    }

    public function test_deactivation_revokes_database_sessions_and_remember_token(): void
    {
        $actor = $this->superAdmin();

        $target = User::factory()->create([
            'remember_token' => 'remember-me',
        ]);

        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $this->actingAs($actor)
            ->patch(
                "/admin/users/{$target->id}/active",
                [
                    'is_active' => false,
                    'lock_version' => $target->lock_version,
                ],
            )
            ->assertSessionHas('success');

        $target->refresh();

        $this->assertFalse(
            $target->is_active,
        );

        $this->assertNull(
            $target->remember_token,
        );

        $this->assertDatabaseMissing(
            'sessions',
            [
                'id' => 'test-session-id',
            ],
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'actor_id' => $actor->id,
                'entity_id' => $target->id,
                'action' => 'user.deactivated',
                'result' => 'SUCCESS',
            ],
        );
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();

        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $user->roles()->attach($role);

        return $user;
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function roleWithPermissions(
        string $name,
        array $permissionNames,
    ): Role {
        $role = Role::query()->create([
            'name' => $name,
            'description' => $name,
            'is_system' => false,
            'lock_version' => 1,
        ]);

        $ids = Permission::query()
            ->whereIn(
                'name',
                $permissionNames,
            )
            ->pluck('id');

        $role->permissions()->sync($ids);

        return $role;
    }
}
