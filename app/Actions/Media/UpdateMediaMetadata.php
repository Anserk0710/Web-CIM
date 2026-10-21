<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateMediaMetadata
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Media $media,
        ?string $defaultAlt,
        ?string $caption,
        int $expectedLockVersion,
    ): Media {
        return DB::transaction(
            function () use ($actor, $media, $defaultAlt, $caption, $expectedLockVersion): Media {
                $locked = Media::query()
                    ->whereKey($media->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Media telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'default_alt' => $defaultAlt,

                    'caption' => $caption,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'media.metadata_updated',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
