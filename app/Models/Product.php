<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends BaseModel
{
    public const REDIRECT_TYPE = 'PRODUCT';

    protected $fillable = [
        'public_slug',
        'status',
        'published_at',
        'archived_at',
        'created_by',
        'updated_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ProductRevision::class);
    }

    public function workingRevision(): HasOne
    {
        return $this->hasOne(ProductRevision::class)
            ->where(
                'kind',
                RevisionKind::Working->value,
            );
    }

    public function publishedRevision(): HasOne
    {
        return $this->hasOne(ProductRevision::class)
            ->where(
                'kind',
                RevisionKind::Published->value,
            );
    }

    public function ecatalogEntries(): HasMany
    {
        return $this->hasMany(
            EcatalogEntry::class,
        );
    }

    public function scopePublished(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'status',
                ContentStatus::Published->value,
            )
            ->whereNotNull('public_slug')
            ->whereNotNull('published_at');
    }
}
