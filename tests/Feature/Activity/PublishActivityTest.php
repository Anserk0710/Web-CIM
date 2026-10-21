<?php

namespace Tests\Feature\Activity;

use App\Actions\Activities\PublishActivity;
use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use App\Models\Activity;
use App\Models\ActivityRevision;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublishActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_copies_blocks_and_related_products_without_changing_working_revision(): void
    {
        $actor = User::factory()->create();
        $activity = Activity::query()->create([
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'lock_version' => 1,
        ]);
        $working = ActivityRevision::query()->create([
            'activity_id' => $activity->id,
            'kind' => RevisionKind::Working,
            'requested_slug' => 'medical-exhibition',
            'title' => 'Medical Exhibition',
            'updated_by' => $actor->id,
        ]);
        $block = $working->blocks()->create([
            'block_key' => (string) Str::uuid(),
            'type' => 'paragraph',
            'data' => ['text' => 'Exhibition details'],
            'sort_order' => 0,
        ]);
        $first = Product::query()->create([
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
        $second = Product::query()->create([
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
        $working->relatedProducts()->sync([
            $first->id => ['sort_order' => 1],
            $second->id => ['sort_order' => 0],
        ]);

        $result = app(PublishActivity::class)->handle($actor, $activity, 1);

        $this->assertSame(ContentStatus::Published, $result->status);
        $this->assertSame('medical-exhibition', $result->public_slug);
        $this->assertSame(2, $result->lock_version);

        $published = $result->publishedRevision;
        $this->assertNotNull($published);
        $this->assertNotSame($working->id, $published->id);
        $this->assertSame($working->title, $published->title);
        $copiedBlock = $published->blocks()->sole();
        $this->assertNotSame($block->id, $copiedBlock->id);
        $this->assertSame($block->block_key, $copiedBlock->block_key);
        $this->assertSame($block->data, $copiedBlock->data);
        $this->assertSame(
            [$second->id, $first->id],
            $published->relatedProducts->modelKeys(),
        );
        $this->assertSame(
            [0, 1],
            $published->relatedProducts->pluck('pivot.sort_order')->all(),
        );
        $this->assertSame($working->id, $result->workingRevision->id);
        $this->assertSame($block->id, $working->blocks()->sole()->id);
        $this->assertSame(
            [$second->id, $first->id],
            $working->fresh()->relatedProducts->modelKeys(),
        );
    }

    public function test_republish_replaces_previous_revision_and_clears_its_block_media_usages(): void
    {
        $actor = User::factory()->create();
        $activity = Activity::query()->create([
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'lock_version' => 1,
        ]);
        $working = ActivityRevision::query()->create([
            'activity_id' => $activity->id,
            'kind' => RevisionKind::Working,
            'requested_slug' => 'medical-exhibition',
            'title' => 'Medical Exhibition',
            'updated_by' => $actor->id,
        ]);
        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'images/exhibition.webp',
            'original_name' => 'exhibition.webp',
            'mime' => 'image/webp',
            'size_bytes' => 10,
            'sha256' => str_repeat('a', 64),
            'uploaded_by' => $actor->id,
        ]);
        $working->blocks()->create([
            'block_key' => (string) Str::uuid(),
            'type' => 'image',
            'data' => ['media_id' => $media->id],
            'sort_order' => 0,
        ]);
        $action = app(PublishActivity::class);
        $first = $action->handle($actor, $activity, 1);
        $oldRevision = $first->publishedRevision;
        $oldBlock = $oldRevision->blocks()->sole();
        $this->assertDatabaseHas('media_usages', [
            'owner_type' => 'activity_block',
            'owner_id' => $oldBlock->id,
            'media_id' => $media->id,
        ]);
        $working->update(['title' => 'Updated Exhibition']);

        $result = $action->handle($actor, $first, 2);

        $published = $result->publishedRevision;
        $this->assertSame(3, $result->lock_version);
        $this->assertNotSame($oldRevision->id, $published->id);
        $this->assertSame('Updated Exhibition', $published->title);
        $this->assertModelMissing($oldRevision);
        $this->assertModelMissing($oldBlock);
        $this->assertDatabaseMissing('media_usages', [
            'owner_type' => 'activity_block',
            'owner_id' => $oldBlock->id,
        ]);
        $this->assertDatabaseHas('media_usages', [
            'owner_type' => 'activity_block',
            'owner_id' => $published->blocks()->sole()->id,
            'media_id' => $media->id,
        ]);
        $this->assertSame($working->id, $result->workingRevision->id);
        $this->assertSame(2, $result->revisions()->count());
    }
}
