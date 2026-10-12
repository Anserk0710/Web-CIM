<?php

namespace Tests\Feature\Product;

use App\Actions\Products\SaveWorkingProduct;
use App\Models\Media;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductMediaSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->admin = User::factory()
            ->create();

        $role = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $this->admin
            ->roles()
            ->attach($role);

        $this->category = ProductCategory::query()
            ->create([
                'name' => 'Category',
                'slug' => 'category',
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
                'lock_version' => 1,
            ]);
    }

    public function test_media_upload_creates_public_media_record(): void
    {
        $disk = Storage::fake('public');

        // A real 1x1 PNG tests MIME detection without requiring GD to generate an image.
        $file = UploadedFile::fake()->createWithContent(
            'product.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN1cAAAAASUVORK5CYII='),
        );

        $this->actingAs($this->admin)
            ->post(
                '/admin/media',
                [
                    'file' => $file,
                    'default_alt' => 'Product image',
                ],
            )
            ->assertSessionHas('success');

        $media = Media::query()
            ->firstOrFail();

        $disk
            ->assertExists($media->path);

        $this->assertSame(
            'public',
            $media->disk,
        );

        $this->assertStringStartsWith(
            'image/',
            $media->mime,
        );
    }

    public function test_media_referenced_by_working_revision_cannot_be_deleted(): void
    {
        $disk = Storage::fake('public');

        Storage::disk('public')->put(
            'media/test.jpg',
            'image',
        );

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
            'original_name' => 'test.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => 5,
            'sha256' => str_repeat('a', 64),
            'uploaded_by' => $this->admin->id,
            'lock_version' => 1,
        ]);

        app(SaveWorkingProduct::class)->handle(
            actor: $this->admin,
            data: [
                'requested_slug' => 'media-test',
                'name' => 'Media Test',
                'summary' => null,
                'description' => null,
                'category_id' => $this->category->id,
                'brand_id' => null,
                'type_id' => null,
                'is_featured' => false,
                'seo_title' => null,
                'seo_description' => null,
                'og_media_id' => null,

                'gallery' => [
                    [
                        'media_id' => $media->id,
                        'alt_text' => 'Test',
                        'caption' => null,
                    ],
                ],

                'brochure_media_id' => null,
                'highlights' => [],
                'spec_groups' => [],
                'related_product_ids' => [],
            ],
        );

        $this->actingAs($this->admin)
            ->delete(
                "/admin/media/{$media->id}",
            )
            ->assertSessionHasErrors(
                'media',
            );

        $this->assertDatabaseHas(
            'media',
            [
                'id' => $media->id,
            ],
        );

        $disk
            ->assertExists(
                'media/test.jpg',
            );
    }

    public function test_unused_media_can_be_deleted(): void
    {
        $disk = Storage::fake('public');

        Storage::disk('public')->put(
            'media/delete.jpg',
            'image',
        );

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'media/delete.jpg',
            'original_name' => 'delete.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => 5,
            'sha256' => str_repeat('b', 64),
            'uploaded_by' => $this->admin->id,
            'lock_version' => 1,
        ]);

        $this->actingAs($this->admin)
            ->delete(
                "/admin/media/{$media->id}",
            )
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'media',
            [
                'id' => $media->id,
            ],
        );

        $disk
            ->assertMissing(
                'media/delete.jpg',
            );
    }
}
