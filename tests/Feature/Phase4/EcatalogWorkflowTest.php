<?php

namespace Tests\Feature\Phase4;

use App\Actions\Products\PublishProduct;
use App\Actions\Products\SaveWorkingProduct;
use App\Models\EcatalogEntry;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcatalogWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $productId;

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

        $category =
            ProductCategory::query()
                ->create([
                    'name' => 'Medical',
                    'slug' => 'medical',
                    'sort_order' => 0,
                    'is_active' => true,
                    'lock_version' => 1,
                ]);

        $product = app(
            SaveWorkingProduct::class,
        )->handle(
            $this->admin,
            [
                'requested_slug' => 'ecatalog-product',

                'name' => 'E-Catalog Product',

                'summary' => null,
                'description' => null,

                'category_id' => $category->id,

                'brand_id' => null,
                'type_id' => null,
                'is_featured' => false,
                'seo_title' => null,
                'seo_description' => null,
                'og_media_id' => null,
                'gallery' => [],
                'brochure_media_id' => null,
                'highlights' => [],
                'spec_groups' => [],
                'related_product_ids' => [],
            ],
        );

        app(
            PublishProduct::class,
        )->handle(
            $this->admin,
            $product,
            $product
                ->refresh()
                ->lock_version,
        );

        $this->productId =
            $product->id;
    }

    public function test_ecatalog_create_update_publish_archive(): void
    {
        $this->actingAs($this->admin)
            ->post(
                '/admin/ecatalog',
                $this->payload(),
            )
            ->assertRedirect();

        $entry = EcatalogEntry::query()
            ->firstOrFail();

        $this->assertSame(
            'DRAFT',
            $entry->status->value,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/ecatalog/{$entry->id}/publish",
                [
                    'lock_version' => $entry
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'PUBLISHED',
            $entry
                ->refresh()
                ->status
                ->value,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/ecatalog/{$entry->id}/archive",
                [
                    'lock_version' => $entry
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            'ARCHIVED',
            $entry
                ->refresh()
                ->status
                ->value,
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'ecatalog.published',
            ],
        );
    }

    public function test_validation_rejects_bad_expiry(): void
    {
        $payload = $this->payload();

        $payload['effective_at'] =
            '2026-10-20 12:00:00';

        $payload['expires_at'] =
            '2026-10-20 11:00:00';

        $this->actingAs($this->admin)
            ->post(
                '/admin/ecatalog',
                $payload,
            )
            ->assertSessionHasErrors(
                'expires_at',
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'product_id' => $this->productId,

            'catalog_code' => 'CAT-001',

            'procurement_title' => 'Procurement Product',

            'procurement_category' => 'Medical',

            'notes' => 'Test',
            'unit_package' => 'Box',

            'availability' => 'CONTACT_US',

            'reference_url' => 'https://example.com/catalog',

            'effective_at' => null,
            'expires_at' => null,
            'sort_order' => 0,

            'seo_title' => 'Catalog SEO',

            'seo_description' => 'Catalog SEO Description',
        ];
    }
}
