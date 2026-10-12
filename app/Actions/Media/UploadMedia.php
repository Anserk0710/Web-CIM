<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class UploadMedia
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        UploadedFile $file,
        ?string $defaultAlt = null,
        ?string $caption = null,
    ): Media {
        $mime = (string) $file->getMimeType();

        $extension = $file->guessExtension()
            ?: strtolower(
                $file->getClientOriginalExtension(),
            );

        $directory = 'media/'
            .now()->format('Y/m');

        $filename = (string) Str::uuid()
            .'.'
            .$extension;

        $path = $directory.'/'.$filename;

        $sha256 = hash_file(
            'sha256',
            $file->getRealPath(),
        );

        $width = null;
        $height = null;

        if (str_starts_with($mime, 'image/')) {
            $dimensions = @getimagesize(
                $file->getRealPath(),
            );

            if ($dimensions !== false) {
                $width = $dimensions[0];
                $height = $dimensions[1];
            }
        }

        $stored = Storage::disk('public')
            ->putFileAs(
                $directory,
                $file,
                $filename,
            );

        if ($stored === false) {
            throw new RuntimeException(
                'Media file could not be stored.',
            );
        }

        try {
            return DB::transaction(
                function () use ($actor, $file, $mime, $path, $sha256, $width, $height, $defaultAlt, $caption): Media {
                    $media = Media::query()->create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $file
                            ->getClientOriginalName(),
                        'mime' => $mime,
                        'size_bytes' => $file->getSize(),
                        'sha256' => $sha256,
                        'width' => $width,
                        'height' => $height,
                        'default_alt' => $defaultAlt,
                        'caption' => $caption,
                        'uploaded_by' => $actor->id,
                        'lock_version' => 1,
                    ]);

                    $this->audit->record(
                        action: 'media.uploaded',
                        entity: $media,
                        changes: [
                            'mime' => $mime,
                            'size_bytes' => $file->getSize(),
                        ],
                        actor: $actor,
                    );

                    return $media;
                },
                attempts: 3,
            );
        } catch (Throwable $exception) {
            Storage::disk('public')
                ->delete($path);

            throw $exception;
        }
    }
}
