<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\PageSection;
use Illuminate\Validation\ValidationException;

class PageSectionMedia
{
    /**
     * Synchronize optional page media inside the page update transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function sync(PageSection $section, array $data, string $errorPrefix): void
    {
        $references = array_filter([
            'image_id' => $data['image_id'] ?? null,
            'video_id' => $data['video_id'] ?? null,
        ]);

        $existing = MediaUsage::query()
            ->where('owner_type', 'page_section')
            ->where('owner_id', $section->id)
            ->whereIn('slot', ['image_id', 'video_id']);

        $ids = $existing->pluck('media_id')->merge(array_values($references))->unique()->sort()->values();
        $media = Media::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

        foreach ($references as $slot => $id) {
            $item = $media->get((int) $id);
            $valid = $item !== null && ($slot === 'video_id'
                ? $item->mime === 'video/mp4'
                : in_array($item->mime, ['image/jpeg', 'image/png', 'image/webp'], true));

            if (! $valid) {
                throw ValidationException::withMessages([
                    "{$errorPrefix}.{$slot}" => 'Berkas sudah tidak tersedia atau jenis media tidak sesuai.',
                ]);
            }
        }

        $existing->delete();

        foreach ($references as $slot => $id) {
            MediaUsage::query()->create([
                'media_id' => (int) $id,
                'owner_type' => 'page_section',
                'owner_id' => $section->id,
                'slot' => $slot,
            ]);
        }
    }
}
