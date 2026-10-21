<?php

namespace Tests\Feature\Phase4;

use App\Models\Career;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_career_draft_open_close_archive(): void
    {
        $this->actingAs($this->admin)
            ->post(
                '/admin/careers',
                $this->payload(),
            )
            ->assertRedirect();

        $career =
            Career::query()
                ->firstOrFail();

        $this->assertSame(
            'DRAFT',
            $career->status->value,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/careers/{$career->id}/open",
                [
                    'lock_version' => $career
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'OPEN',
            $career
                ->refresh()
                ->status
                ->value,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/careers/{$career->id}/close",
                [
                    'lock_version' => $career
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'CLOSED',
            $career
                ->refresh()
                ->status
                ->value,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/careers/{$career->id}/archive",
                [
                    'lock_version' => $career
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'ARCHIVED',
            $career
                ->refresh()
                ->status
                ->value,
        );
    }

    public function test_wita_input_is_stored_in_utc(): void
    {
        $this->actingAs($this->admin)
            ->post(
                '/admin/careers',
                $this->payload(),
            );

        $career =
            Career::query()
                ->firstOrFail();

        $this->assertSame(
            '2026-10-20 01:00:00',
            $career
                ->opens_at
                ->format(
                    'Y-m-d H:i:s',
                ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'slug' => 'software-engineer',

            'title' => 'Software Engineer',

            'department' => 'Technology',

            'location' => 'Denpasar',

            'employment_type' => 'FULL_TIME',

            'summary' => 'Summary',

            'responsibilities' => 'Responsibilities',

            'requirements' => 'Requirements',

            'opens_at_local' => '2026-10-20T09:00',

            'closes_at_local' => '2026-11-20T17:00',

            'seo_title' => 'Software Engineer',

            'seo_description' => 'Career opening',
        ];
    }
}
