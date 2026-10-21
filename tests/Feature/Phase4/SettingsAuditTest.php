<?php

namespace Tests\Feature\Phase4;

use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SitePageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
            SitePageSeeder::class,
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

    public function test_settings_are_persisted_and_audited(): void
    {
        $this->actingAs($this->admin)
            ->put(
                '/admin/settings',
                [
                    'company' => [
                        'legal_name' => 'PT Cahya Intan Medika',

                        'display_name' => 'CIM',

                        'about' => 'Medical company',
                    ],

                    'contact' => [
                        'email' => 'info@example.test',

                        'phone' => '123',

                        'whatsapp' => '123',
                    ],

                    'coverage' => [
                        'regions' => [
                            'Bali',
                        ],
                    ],

                    'seo_defaults' => [
                        'title_suffix' => 'CIM',

                        'default_description' => 'Medical',
                    ],

                    'branding' => [
                        'logo_media_id' => null,

                        'favicon_media_id' => null,
                    ],

                    'footer' => [
                        'tagline' => 'Healthcare',

                        'copyright' => 'CIM',
                    ],
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'CIM',
            SiteSetting::query()
                ->where(
                    'key',
                    'company',
                )
                ->firstOrFail()
                ->value[
                'display_name'
            ],
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'settings.updated',
            ],
        );
    }

    public function test_audit_page_requires_permission(): void
    {
        $readOnly =
            User::factory()->create();

        $role = Role::query()
            ->where(
                'name',
                'Read Only',
            )
            ->firstOrFail();

        $readOnly
            ->roles()
            ->attach($role);

        $this->actingAs($readOnly)
            ->get('/admin/audit')
            ->assertForbidden();

        $this->withoutVite();

        $this->actingAs($this->admin)
            ->get('/admin/audit')
            ->assertOk();
    }
}
