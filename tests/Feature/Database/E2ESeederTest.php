<?php

namespace Tests\Feature\Database;

use App\Enums\RevisionKind;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRevision;
use App\Models\UrlRedirect;
use App\Models\User;
use Database\Seeders\E2ESeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ESeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_seeding_cleans_incoming_product_references_and_preserves_other_data(): void
    {
        $this->seed(E2ESeeder::class);

        $e2eUser = User::query()
            ->where('email', (string) env('E2E_USER_EMAIL', 'e2e@cim.test'))
            ->firstOrFail();
        $otherUser = User::factory()->create();
        $fixture = $this->createProduct($e2eUser);
        $other = $this->createProduct($otherUser);
        $unrelated = $this->createProduct($otherUser);

        foreach ([RevisionKind::Working, RevisionKind::Published] as $kind) {
            $fixtureRevision = $this->createRevision($fixture, $kind);
            $otherRevision = $this->createRevision($other, $kind);

            $fixtureRevision->relatedProducts()->attach($other->id);
            $otherRevision->relatedProducts()->attach([
                $fixture->id => ['sort_order' => 0],
                $unrelated->id => ['sort_order' => 1],
            ]);
        }

        $redirect = UrlRedirect::query()->create([
            'entity_type' => Product::REDIRECT_TYPE,
            'entity_id' => $fixture->id,
            'old_path' => '/products/old-e2e-product',
            'created_at' => now(),
        ]);

        $this->seed(E2ESeeder::class);
        $this->seed(E2ESeeder::class);

        $this->assertModelMissing($fixture);
        $this->assertModelMissing($redirect);
        $this->assertDatabaseMissing('product_revisions', ['product_id' => $fixture->id]);
        $this->assertDatabaseMissing('product_revision_related', ['related_product_id' => $fixture->id]);
        $this->assertModelExists($other);
        $this->assertModelExists($unrelated);
        $this->assertSame(2, Product::query()->count());
        $this->assertSame(2, ProductRevision::query()->count());

        foreach ($other->revisions as $revision) {
            $this->assertSame([$unrelated->id], $revision->relatedProducts()->pluck('products.id')->all());
            $this->assertDatabaseHas('product_revision_related', [
                'revision_id' => $revision->id,
                'related_product_id' => $unrelated->id,
                'sort_order' => 1,
            ]);
        }

        $this->assertSame(1, ProductCategory::query()->where('slug', 'e2e-category')->count());
    }

    private function createProduct(User $user): Product
    {
        return Product::query()->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    private function createRevision(Product $product, RevisionKind $kind): ProductRevision
    {
        return $product->revisions()->create([
            'kind' => $kind,
            'requested_slug' => 'product-'.$product->id,
            'name' => 'Product '.$product->id,
            'category_id' => ProductCategory::query()->where('slug', 'e2e-category')->sole()->id,
            'updated_by' => $product->updated_by,
        ]);
    }
}
