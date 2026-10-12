<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class Media extends BaseModel
{
    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'sha256',
        'width',
        'height',
        'default_alt',
        'caption',
        'uploaded_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Media $media): void {
            if ($media->disk !== 'public') {
                throw new InvalidArgumentException(
                    'Media Library hanya boleh menggunakan disk public.',
                );
            }
        });
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function scopeImages(
        Builder $query,
    ): Builder {
        return $query->where(
            'mime',
            'like',
            'image/%',
        );
    }

    public function scopePdf(
        Builder $query,
    ): Builder {
        return $query->where(
            'mime',
            'application/pdf',
        );
    }

    public function url(): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($this->path);
    }
}
