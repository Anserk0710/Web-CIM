<?php

namespace Tests\Feature\Admin;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    public function test_admin_login_shell_is_available(): void
    {
        $this->withoutVite();

        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Login')
                    ->where('phase', 'Phase 1')
            );
    }

    public function test_admin_dashboard_shell_is_available(): void
    {
        $this->withoutVite();

        $response = $this->get('/admin');

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Dashboard')
                    ->where('phase', 'Phase 1')
            );
    }
}
