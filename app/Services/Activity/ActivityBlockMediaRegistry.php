<?php

namespace App\Services\Activity;

use App\Models\ActivityBlock;
use App\Models\ActivityRevision;
use App\Models\Media;
use App\Services\Media\MediaUsageRegistry;
use Illuminate\Support\Facades\DB;

class ActivityBlockMediaRegistry
{
    public function __construct(
        private readonly MediaUsageRegistry $registry,
    ) {}

    public function clearRevision(
        ActivityRevision $revision,
    ): void {
        $ids = $revision
            ->blocks()
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('media_usages')
            ->where(
                'owner_type',
                'activity_block',
            )
            ->whereIn(
                'owner_id',
                $ids,
            )
            ->delete();
    }

    public function syncBlock(
        ActivityBlock $block,
    ): void {
        DB::table('media_usages')
            ->where(
                'owner_type',
                'activity_block',
            )
            ->where(
                'owner_id',
                $block->id,
            )
            ->delete();

        $mediaIds = match ($block->type) {
            'image' => [
                (int) (
                    $block->data[
                        'media_id'
                    ] ?? 0
                ),
            ],

            'gallery' => array_map(
                'intval',
                $block->data[
                    'media_ids'
                ] ?? [],
            ),

            default => [],
        };

        foreach (
            array_values(
                array_filter(
                    array_unique(
                        $mediaIds,
                    ),
                ),
            ) as $index => $mediaId
        ) {
            $media = Media::query()
                ->findOrFail($mediaId);

            $this->registry->register(
                media: $media,
                ownerType: 'activity_block',
                ownerId: $block->id,
                slot: 'media.'.$index,
            );
        }
    }
}
