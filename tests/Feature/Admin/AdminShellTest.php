<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminShellTest extends TestCase
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

    public function test_admin_login_shell_is_available(): void
    {
        $this->withoutVite();

        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Auth/Login')
            );
    }

    public function test_admin_dashboard_shell_is_available(): void
    {
        $this->withoutVite();

        $user = $this->superAdmin();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Dashboard')
                    ->where('phase', 'Phase 2')
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
}
