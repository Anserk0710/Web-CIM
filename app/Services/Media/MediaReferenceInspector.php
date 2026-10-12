<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class MediaReferenceInspector
{
    public function assertUnusedLocked(
        Media $media,
    ): Media {
        if (DB::transactionLevel() < 1) {
            throw new LogicException(
                'Media reference check wajib dilakukan di dalam database transaction.',
            );
        }

        $locked = Media::query()
            ->whereKey($media->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (
            DB::table('media_usages')
                ->where(
                    'media_id',
                    $locked->id,
                )
                ->exists()
            || DB::table('product_revision_media')
                ->where(
                    'media_id',
                    $locked->id,
                )
                ->exists()
            || DB::table('product_revisions')
                ->where(
                    'og_media_id',
                    $locked->id,
                )
                ->exists()
            || DB::table('activity_revisions')
                ->where(
                    'featured_media_id',
                    $locked->id,
                )
                ->orWhere(
                    'og_media_id',
                    $locked->id,
                )
                ->exists()
            || DB::table('site_pages')
                ->where(
                    'og_media_id',
                    $locked->id,
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'media' => 'Media masih digunakan dan tidak dapat dihapus.',
            ]);
        }

        return $locked;
    }
}
