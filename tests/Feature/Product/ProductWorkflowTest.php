<?php

namespace Tests\Feature\Product;

use App\Actions\Products\PublishProduct;
use App\Actions\Products\SaveWorkingProduct;
use App\Actions\Products\UnpublishProduct;
use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private ProductCategory $category;

    private Brand $brand;

    private ProductType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->superAdmin = User::factory()
            ->create();

        $superAdminRole = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $this->superAdmin
            ->roles()
            ->attach($superAdminRole);

        $this->category = ProductCategory::query()
            ->create([
                'name' => 'Medical Device',
                'slug' => 'medical-device',
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
                'lock_version' => 1,
            ]);

        $this->brand = Brand::query()->create([
            'name' => 'CIM',
            'slug' => 'cim',
            'is_active' => true,
            'lock_version' => 1,
        ]);

        $this->type = ProductType::query()
            ->create([
                'name' => 'Monitor',
                'slug' => 'monitor',
                'is_active' => true,
                'lock_version' => 1,
            ]);
    }

    public function test_create_product_stores_complete_working_revision(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(
                '/admin/products',
                $this->payload(
                    slug: 'patient-monitor',
                    name: 'Patient Monitor',
                ),
            )
            ->assertRedirect();

        $product = Product::query()
            ->with([
                'workingRevision.highlights',
                'workingRevision.specificationGroups.items',
            ])
            ->firstOrFail();

        $this->assertSame(
            ContentStatus::Draft,
            $product->status,
        );

        $this->assertSame(
            'Patient Monitor',
            $product->workingRevision->name,
        );

        $this->assertSame(
            $this->category->id,
            $product
                ->workingRevision
                ->category_id,
        );

        $this->assertSame(
            2,
            $product
                ->workingRevision
                ->highlights
                ->count(),
        );

        $this->assertSame(
            'Display',
            $product
                ->workingRevision
                ->specificationGroups
                ->first()
                ->items
                ->first()
                ->label,
        );
    }

    public function test_draft_edit_does_not_modify_live_revision(): void
    {
        $product = $this->createProduct(
            'live-monitor',
            'Live Monitor',
        );

        app(PublishProduct::class)->handle(
            actor: $this->superAdmin,
            product: $product,
            expectedLockVersion: $product
                ->refresh()
                ->lock_version,
        );

        $publishedId = $product
            ->refresh()
            ->publishedRevision
            ->id;

        $payload = $this->payload(
            slug: 'live-monitor',
            name: 'Draft Changed Name',
        );

        $payload['expected_lock_version'] =
            $product->lock_version;

        $payload['highlights'] = ['Draft highlight'];
        $payload['spec_groups'][0]['items'][0]['value'] = '20';

        app(SaveWorkingProduct::class)->handle(
            actor: $this->superAdmin,
            data: $payload,
            product: $product,
        );

        $product->refresh();

        $this->assertSame(
            'Draft Changed Name',
            $product
                ->workingRevision
                ->name,
        );

        $this->assertSame(
            'Live Monitor',
            $product
                ->publishedRevision
                ->name,
        );

        $this->assertSame(
            $publishedId,
            $product
                ->publishedRevision
                ->id,
        );

        $this->assertSame('Draft highlight', $product->workingRevision->highlights->first()->text);
        $this->assertSame('Highlight one', $product->publishedRevision->highlights->first()->text);
        $this->assertSame('20', $product->workingRevision->specificationGroups->first()->items->first()->value);
        $this->assertSame('15', $product->publishedRevision->specificationGroups->first()->items->first()->value);
    }

    public function test_unpublish_hides_product_and_retains_published_revision(): void
    {
        $product = $this->createProduct('unpublish-test', 'Unpublish Test');

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $product,
            $product->lock_version,
        );

        $revisionId = $product->refresh()->publishedRevision->id;

        app(UnpublishProduct::class)->handle(
            $this->superAdmin,
            $product,
            $product->lock_version,
        );

        $product->refresh();

        $this->assertSame(ContentStatus::Draft, $product->status);
        $this->assertNull($product->published_at);
        $this->assertSame($revisionId, $product->publishedRevision->id);
        $this->get('/products/unpublish-test')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/products/unpublish-test', false);
    }

    public function test_editor_without_publish_permission_is_forbidden_even_with_crafted_request(): void
    {
        $editor = User::factory()->create();

        $role = Role::query()
            ->where('name', 'Editor')
            ->firstOrFail();

        $editor->roles()->attach($role);

        $product = $this->createProduct(
            'editor-test',
            'Editor Test',
        );

        $this->actingAs($editor)
            ->post(
                "/admin/products/{$product->id}/publish",
                [
                    'lock_version' => $product
                        ->refresh()
                        ->lock_version,
                ],
            )
            ->assertForbidden();

        $this->assertNull(
            $product
                ->refresh()
                ->published_at,
        );
    }

    public function test_stale_edit_is_rejected(): void
    {
        $product = $this->createProduct(
            'locking-test',
            'Locking Test',
        );

        $staleVersion = $product
            ->refresh()
            ->lock_version;

        $first = $this->payload(
            'locking-test',
            'First Update',
        );

        $first['expected_lock_version'] =
            $staleVersion;

        $this->actingAs($this->superAdmin)
            ->put(
                "/admin/products/{$product->id}",
                $first,
            )
            ->assertSessionHas(
                'success',
            );

        $second = $this->payload(
            'locking-test',
            'Stale Update',
        );

        $second['expected_lock_version'] =
            $staleVersion;

        $this->actingAs($this->superAdmin)
            ->put(
                "/admin/products/{$product->id}",
                $second,
            )
            ->assertSessionHasErrors(
                'expected_lock_version',
            );

        $this->assertSame(
            'First Update',
            $product
                ->refresh()
                ->workingRevision
                ->name,
        );
    }

    public function test_slug_collision_rolls_back_publish(): void
    {
        $first = $this->createProduct(
            'collision',
            'First',
        );

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $first,
            $first->refresh()->lock_version,
        );

        $second = $this->createProduct(
            'collision',
            'Second',
        );

        $this->actingAs($this->superAdmin)
            ->post(
                "/admin/products/{$second->id}/publish",
                [
                    'lock_version' => $second
                        ->refresh()
                        ->lock_version,
                ],
            )
            ->assertSessionHasErrors(
                'requested_slug',
            );

        $second->refresh();

        $this->assertSame(
            ContentStatus::Draft,
            $second->status,
        );

        $this->assertNull(
            $second->publishedRevision,
        );
    }

    public function test_slug_change_creates_permanent_redirect(): void
    {
        $product = $this->createProduct(
            'old-product-slug',
            'Redirect Test',
        );

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $product,
            $product->refresh()->lock_version,
        );

        $payload = $this->payload(
            'new-product-slug',
            'Redirect Test',
        );

        $payload['expected_lock_version'] =
            $product->refresh()->lock_version;

        app(SaveWorkingProduct::class)->handle(
            $this->superAdmin,
            $payload,
            $product,
        );

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $product,
            $product->refresh()->lock_version,
        );

        $this->get('/products/old-product-slug')
            ->assertRedirect(
                '/products/new-product-slug',
            )
            ->assertStatus(301);
    }

    public function test_archive_hides_public_product_without_deleting_revisions(): void
    {
        $product = $this->createProduct(
            'archive-test',
            'Archive Test',
        );

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $product,
            $product->refresh()->lock_version,
        );

        $publishedRevisionId = $product
            ->refresh()
            ->publishedRevision
            ->id;

        $this->withoutVite();

        $this->get(
            '/products/archive-test',
        )->assertOk();

        $this->actingAs($this->superAdmin)
            ->post(
                "/admin/products/{$product->id}/archive",
                [
                    'lock_version' => $product
                        ->refresh()
                        ->lock_version,
                ],
            )
            ->assertSessionHas('success');

        $this->get(
            '/products/archive-test',
        )->assertNotFound();

        $this->assertDatabaseHas(
            'product_revisions',
            [
                'id' => $publishedRevisionId,
                'kind' => RevisionKind::Published->value,
            ],
        );
    }

    public function test_sitemap_only_exposes_published_products(): void
    {
        $published = $this->createProduct(
            'sitemap-live',
            'Sitemap Live',
        );

        app(PublishProduct::class)->handle(
            $this->superAdmin,
            $published,
            $published->refresh()->lock_version,
        );

        $this->createProduct(
            'sitemap-draft',
            'Sitemap Draft',
        );

        $response = $this->get(
            '/sitemap.xml',
        );

        $response
            ->assertOk()
            ->assertSee(
                '/products/sitemap-live',
                false,
            )
            ->assertDontSee(
                '/products/sitemap-draft',
                false,
            );
    }

    public function test_public_listing_uses_twelve_items_per_page(): void
    {
        foreach (range(1, 13) as $index) {
            $product = $this->createProduct(
                "pagination-{$index}",
                "Pagination {$index}",
            );

            app(PublishProduct::class)->handle(
                $this->superAdmin,
                $product,
                $product
                    ->refresh()
                    ->lock_version,
            );
        }

        $this->withoutVite();

        $this->get('/products')
            ->assertOk()
            ->assertViewHas(
                'products',
                fn ($products): bool => $products
                    ->count() === 12,
            );
    }

    private function createProduct(
        string $slug,
        string $name,
    ): Product {
        return app(
            SaveWorkingProduct::class,
        )->handle(
            actor: $this->superAdmin,
            data: $this->payload(
                $slug,
                $name,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(
        string $slug,
        string $name,
    ): array {
        return [
            'expected_lock_version' => null,
            'requested_slug' => $slug,
            'name' => $name,
            'summary' => 'Summary '.$name,
            'description' => 'Description '.$name,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'type_id' => $this->type->id,
            'is_featured' => false,
            'seo_title' => $name,
            'seo_description' => 'SEO '.$name,
            'og_media_id' => null,
            'gallery' => [],
            'brochure_media_id' => null,

            'highlights' => [
                'Highlight one',
                'Highlight two',
            ],

            'spec_groups' => [
                [
                    'name' => 'Technical',
                    'items' => [
                        [
                            'label' => 'Display',
                            'value' => '15',
                            'unit' => 'inch',
                        ],
                    ],
                ],
            ],

            'related_product_ids' => [],
        ];
    }
}
