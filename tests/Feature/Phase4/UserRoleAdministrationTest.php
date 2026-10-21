<?php

namespace Tests\Feature\Phase4;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserRoleAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->admin =
            User::factory()->create();

        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $this->admin
            ->roles()
            ->attach($role);
    }

    public function test_super_admin_can_create_user_without_committed_password(): void
    {
        $this->actingAs($this->admin)
            ->post(
                '/admin/users',
                [
                    'name' => 'New Editor',

                    'email' => 'editor@example.test',
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $user = User::query()
            ->where(
                'email',
                'editor@example.test',
            )
            ->firstOrFail();

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'user.created',
            ],
        );
    }

    public function test_role_manage_permission_still_cannot_mutate_super_admin_semantics(): void
    {
        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $this->actingAs($this->admin)
            ->put(
                "/admin/roles/{$role->id}",
                [
                    'name' => 'Changed',

                    'description' => 'Changed',

                    'lock_version' => $role
                        ->lock_version,
                ],
            )
            ->assertForbidden();
    }
}
