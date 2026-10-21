<?php

namespace Tests\Feature\Phase4;

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ActivityCategory $category;

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

        $this->category =
            ActivityCategory::query()
                ->create([
                    'name' => 'News',
                    'slug' => 'news',
                    'is_active' => true,
                    'lock_version' => 1,
                ]);
    }

    public function test_activity_working_publish_archive_flow(): void
    {
        $this->actingAs($this->admin)
            ->post(
                '/admin/activities',
                $this->payload(),
            )
            ->assertRedirect();

        $activity =
            Activity::query()
                ->with(
                    'workingRevision.blocks',
                )
                ->firstOrFail();

        $this->assertSame(
            'DRAFT',
            $activity->status->value,
        );

        $this->assertCount(
            2,
            $activity
                ->workingRevision
                ->blocks,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/activities/{$activity->id}/publish",
                [
                    'lock_version' => $activity
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $activity->refresh();

        $this->assertSame(
            'PUBLISHED',
            $activity->status->value,
        );

        $this->assertNotNull(
            $activity
                ->publishedRevision,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/activities/{$activity->id}/archive",
                [
                    'lock_version' => $activity
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'ARCHIVED',
            $activity
                ->refresh()
                ->status
                ->value,
        );
    }

    public function test_block_html_is_removed_server_side(): void
    {
        $payload = $this->payload();

        $payload['blocks'][0]['data']['text'] =
            '<script>alert(1)</script>Hello';

        $this->actingAs($this->admin)
            ->post(
                '/admin/activities',
                $payload,
            );

        $activity =
            Activity::query()
                ->with(
                    'workingRevision.blocks',
                )
                ->firstOrFail();

        $this->assertStringNotContainsString(
            '<script>',
            $activity
                ->workingRevision
                ->blocks
                ->first()
                ->data['text'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'requested_slug' => 'activity-test',

            'title' => 'Activity Test',

            'excerpt' => 'Excerpt',

            'category_id' => $this->category->id,

            'featured_media_id' => null,
            'is_featured' => false,

            'seo_title' => 'Activity SEO',

            'seo_description' => 'Activity Description',

            'og_media_id' => null,

            'blocks' => [
                [
                    'type' => 'paragraph',

                    'data' => [
                        'text' => 'Paragraph text',
                    ],
                ],

                [
                    'type' => 'heading',

                    'data' => [
                        'text' => 'Heading',

                        'level' => 2,
                    ],
                ],
            ],

            'related_product_ids' => [],
        ];
    }
}
