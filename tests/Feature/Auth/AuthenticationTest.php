<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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

    public function test_guest_is_denied_admin_route(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_valid_active_user_can_log_in(): void
    {
        $user = $this->readOnlyUser();

        $response = $this->post(
            '/admin/login',
            [
                'email' => $user->email,
                'password' => 'password',
            ],
        );

        $response->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_is_denied_login(): void
    {
        $user = User::factory()
            ->inactive()
            ->create();

        $response = $this->post(
            '/admin/login',
            [
                'email' => $user->email,
                'password' => 'password',
            ],
        );

        $response->assertSessionHasErrors(
            'email',
        );

        $this->assertGuest();
    }

    public function test_public_registration_is_not_available(): void
    {
        $this->get('/register')
            ->assertNotFound();

        $this->post('/register')
            ->assertNotFound();
    }

    public function test_user_can_logout(): void
    {
        $user = $this->readOnlyUser();

        $this->actingAs($user)
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    public function test_password_reset_notification_is_sent(): void
    {
        Notification::fake();

        $user = $this->readOnlyUser();

        $this->post(
            '/admin/forgot-password',
            [
                'email' => $user->email,
            ],
        )
            ->assertSessionHas('status');

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
        );
    }

    public function test_unknown_reset_email_gets_same_public_message(): void
    {
        Notification::fake();

        $response = $this->post(
            '/admin/forgot-password',
            [
                'email' => 'unknown@example.test',
            ],
        );

        $response->assertSessionHas(
            'status',
            'Jika alamat email terdaftar dan aktif, tautan reset password telah dikirim.',
        );
    }

    public function test_password_can_be_reset(): void
    {
        Notification::fake();

        $user = $this->readOnlyUser();

        $this->post(
            '/admin/forgot-password',
            [
                'email' => $user->email,
            ],
        );

        $token = null;

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            },
        );

        $this->post(
            '/admin/reset-password',
            [
                'token' => $token,
                'email' => $user->email,
                'password' => 'NewPassword!2026',
                'password_confirmation' => 'NewPassword!2026',
            ],
        )->assertRedirect('/admin/login');

        $this->assertTrue(
            Hash::check(
                'NewPassword!2026',
                $user->refresh()->password,
            ),
        );
    }

    public function test_authenticated_user_can_change_own_password(): void
    {
        $user = $this->readOnlyUser();

        $this->actingAs($user)
            ->put(
                '/admin/change-password',
                [
                    'current_password' => 'password',
                    'password' => 'ChangedPassword!2026',
                    'password_confirmation' => 'ChangedPassword!2026',
                ],
            )
            ->assertSessionHas('success');

        $this->assertTrue(
            Hash::check(
                'ChangedPassword!2026',
                $user->refresh()->password,
            ),
        );
    }

    private function readOnlyUser(): User
    {
        $user = User::factory()->create();

        $role = Role::query()
            ->where('name', 'Read Only')
            ->firstOrFail();

        $user->roles()->attach($role);

        return $user;
    }
}
