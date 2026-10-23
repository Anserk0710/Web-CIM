<?php // path: tests/Feature/Phase5/ProductSeoStructuredDataTest.php

namespace Tests\Feature\Phase5;

use App\Enums\ContentStatus;
use App\Enums\ECatalogAvailability;
use App\Enums\RevisionKind;
use App\Models\Brand;
use App\Models\EcatalogEntry;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeoStructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_product_has_canonical_og_and_product_json_ld(): void
    {
        $product = $this->createPublishedProduct(
            'seo-product-test',
        );

        $response = $this->get(
            route(
                'products.show',
                $product->public_slug,
            ),
        );

        $response
            ->assertOk()
            ->assertSee(
                '<link rel="canonical"',
                false,
            )
            ->assertSee(
                'property="og:type" content="product"',
                false,
            )
            ->assertSee(
                'application/ld+json',
                false,
            )
            ->assertSee(
                '"@type":"Product"',
                false,
            )
            ->assertSee(
                'SEO Test Product',
            );
    }

    public function test_draft_product_does_not_appear_in_sitemap(): void
    {
        $published = $this->createPublishedProduct(
            'visible-seo-product',
        );

        $user = User::factory()->create();

        Product::query()->create([
            'public_slug' => null,

            'status' => ContentStatus::Draft,

            'published_at' => null,

            'archived_at' => null,

            'created_by' => $user->id,

            'updated_by' => $user->id,

            'lock_version' => 1,
        ]);

        $response = $this->get(
            route('sitemap'),
        );

        $response
            ->assertOk()
            ->assertSee(
                route(
                    'products.show',
                    $published->public_slug,
                ),
                false,
            )
            ->assertSee(
                route('ecatalog.public.index'),
                false,
            )
            ->assertDontSee(
                '/products/draft-seo-product',
                false,
            );
    }

    public function test_only_currently_visible_ecatalog_entries_appear_in_listing_and_json_ld(): void
    {
        $product = $this->createPublishedProduct(
            'ecatalog-seo-product',
        );

        $visible = $this->createCatalogEntry(
            $product,
            'CAT-SEO-VISIBLE',
            now()->subDay(),
            now()->addDay(),
        );

        $this->createCatalogEntry(
            $product,
            'CAT-SEO-EXPIRED',
            now()->subDays(10),
            now()->subSecond(),
        );

        $response = $this->get(
            route('ecatalog.public.index'),
        );

        $response
            ->assertOk()
            ->assertSee(
                'CAT-SEO-VISIBLE',
            )
            ->assertDontSee(
                'CAT-SEO-EXPIRED',
            )
            ->assertSee(
                'application/ld+json',
                false,
            )
            ->assertSee(
                '"@type":"ItemList"',
                false,
            );

        $this->assertDatabaseHas(
            'ecatalog_entries',
            [
                'id' => $visible->id,
                'status' => ContentStatus::Published->value,
            ],
        );
    }

    private function createPublishedProduct(
        string $slug,
    ): Product {
        $user = User::factory()->create();

        $category = ProductCategory::query()->create([
            'name' => 'Test Category',

            'slug' => $slug.'-category',

            'description' => 'Synthetic test category.',

            'sort_order' => 0,

            'is_active' => true,

            'lock_version' => 1,
        ]);

        $brand = Brand::query()->create([
            'name' => 'Test Brand',

            'slug' => $slug.'-brand',

            'is_active' => true,

            'lock_version' => 1,
        ]);

        $product = Product::query()->create([
            'public_slug' => $slug,

            'status' => ContentStatus::Published,

            'published_at' => now()->subDay(),

            'archived_at' => null,

            'created_by' => $user->id,

            'updated_by' => $user->id,

            'lock_version' => 1,
        ]);

        ProductRevision::query()->create([
            'product_id' => $product->id,

            'kind' => RevisionKind::Published,

            'requested_slug' => $slug,

            'name' => 'SEO Test Product',

            'summary' => 'Synthetic product for SEO regression tests.',

            'description' => 'Synthetic product description.',

            'category_id' => $category->id,

            'brand_id' => $brand->id,

            'type_id' => null,

            'is_featured' => false,

            'seo_title' => 'SEO Test Product — CIM',

            'seo_description' => 'Synthetic SEO description.',

            'og_media_id' => null,

            'updated_by' => $user->id,
        ]);

        return $product->refresh();
    }

    private function createCatalogEntry(
        Product $product,
        string $code,
        $effectiveAt,
        $expiresAt,
    ): EcatalogEntry {
        return EcatalogEntry::query()->create([
            'product_id' => $product->id,

            'catalog_code' => $code,

            'procurement_title' =>
                'Synthetic procurement item '.$code,

            'procurement_category' => 'Test Category',

            'notes' => 'Synthetic E-Catalog note.',

            'unit_package' => '1 unit',

            'availability' =>
                ECatalogAvailability::ContactUs,

            'reference_url' => null,

            'effective_at' => $effectiveAt,

            'expires_at' => $expiresAt,

            'status' => ContentStatus::Published,

            'sort_order' => 0,

            'seo_title' => 'Synthetic E-Catalog SEO title',

            'seo_description' => 'Synthetic E-Catalog SEO description.',

            'published_at' => now()->subDay(),

            'lock_version' => 1,
        ]);
    }
}
