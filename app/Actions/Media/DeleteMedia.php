<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Media\MediaReferenceInspector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DeleteMedia
{
    public function __construct(
        private readonly MediaReferenceInspector $references,
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Media $media,
    ): void {
        $disk = Storage::disk('public');

        $source = null;
        $quarantine = null;
        $moved = false;

        try {
            DB::transaction(
                function () use ($actor, $media, $disk, &$source, &$quarantine, &$moved): void {
                    $locked = $this
                        ->references
                        ->assertUnusedLocked(
                            $media,
                        );

                    $source = $locked->path;

                    if ($disk->exists($source)) {
                        $quarantine = '.trash/'
                            .Str::uuid();

                        if (
                            ! $disk->move(
                                $source,
                                $quarantine,
                            )
                        ) {
                            throw new RuntimeException(
                                'Media file could not be moved to deletion quarantine.',
                            );
                        }

                        $moved = true;
                    }

                    $this->audit->record(
                        action: 'media.deleted',
                        entity: $locked,
                        changes: [
                            'original_name' => $locked->original_name,
                            'mime' => $locked->mime,
                        ],
                        actor: $actor,
                    );

                    $locked->delete();
                },
                attempts: 3,
            );
        } catch (Throwable $exception) {
            if (
                $moved
                && $source !== null
                && $quarantine !== null
                && $disk->exists($quarantine)
            ) {
                $disk->move(
                    $quarantine,
                    $source,
                );
            }

            throw $exception;
        }

        if (
            $moved
            && $quarantine !== null
        ) {
            $disk->delete($quarantine);
        }
    }
}
