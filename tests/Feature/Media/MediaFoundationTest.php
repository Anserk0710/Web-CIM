<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaReferenceInspector;
use App\Services\Media\MediaUsageRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class MediaFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_library_rejects_private_disk_records(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $user = User::factory()->create();

        Media::query()->create([
            'disk' => 'private',
            'path' => 'secret.pdf',
            'original_name' => 'secret.pdf',
            'mime' => 'application/pdf',
            'size_bytes' => 10,
            'sha256' => str_repeat('a', 64),
            'uploaded_by' => $user->id,
            'lock_version' => 1,
        ]);
    }

    public function test_registry_reference_blocks_media_deletion_check(): void
    {
        $user = User::factory()->create();

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'images/example.webp',
            'original_name' => 'example.webp',
            'mime' => 'image/webp',
            'size_bytes' => 10,
            'sha256' => str_repeat('b', 64),
            'width' => 100,
            'height' => 100,
            'uploaded_by' => $user->id,
            'lock_version' => 1,
        ]);

        app(MediaUsageRegistry::class)->register(
            media: $media,
            ownerType: 'page_section',
            ownerId: 1,
            slot: 'hero.image',
        );

        $this->expectException(
            ValidationException::class,
        );

        DB::transaction(
            function () use ($media): void {
                app(
                    MediaReferenceInspector::class,
                )->assertUnusedLocked(
                    $media,
                );
            },
        );
    }

    public function test_private_and_public_storage_are_separate(): void
    {
        $this->assertNotSame(
            config('filesystems.disks.private.root'),
            config('filesystems.disks.public.root'),
        );

        $this->assertStringEndsWith(
            'storage/app/private',
            str_replace(
                '\\',
                '/',
                config(
                    'filesystems.disks.private.root',
                ),
            ),
        );

        $this->assertStringEndsWith(
            'storage/app/public',
            str_replace(
                '\\',
                '/',
                config(
                    'filesystems.disks.public.root',
                ),
            ),
        );
    }
}
