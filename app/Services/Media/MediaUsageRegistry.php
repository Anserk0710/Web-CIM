<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaUsageRegistry
{
    public function register(
        Media $media,
        string $ownerType,
        int $ownerId,
        string $slot,
    ): MediaUsage {
        $this->assertOwnerType(
            $ownerType,
        );

        return DB::transaction(
            function () use ($media, $ownerType, $ownerId, $slot): MediaUsage {
                Media::query()
                    ->whereKey($media->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                return MediaUsage::query()
                    ->firstOrCreate([
                        'media_id' => $media->id,
                        'owner_type' => $ownerType,
                        'owner_id' => $ownerId,
                        'slot' => $slot,
                    ]);
            },
            attempts: 3,
        );
    }

    public function unregister(
        Media $media,
        string $ownerType,
        int $ownerId,
        string $slot,
    ): void {
        $this->assertOwnerType(
            $ownerType,
        );

        DB::transaction(
            function () use ($media, $ownerType, $ownerId, $slot): void {
                Media::query()
                    ->whereKey($media->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                MediaUsage::query()
                    ->where('media_id', $media->id)
                    ->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('slot', $slot)
                    ->delete();
            },
            attempts: 3,
        );
    }

    private function assertOwnerType(
        string $ownerType,
    ): void {
        if (
            ! in_array(
                $ownerType,
                config(
                    'media.registry_owner_types',
                    [],
                ),
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'owner_type' => 'Media owner type tidak diizinkan.',
            ]);
        }
    }
}
