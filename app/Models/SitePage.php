<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends BaseModel
{
    protected $fillable = [
        'key',
        'title',
        'seo_title',
        'seo_description',
        'og_media_id',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'lock_version' => 'integer',
        ];
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'og_media_id',
        );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(
            PageSection::class,
            'page_id',
        )->orderBy('sort_order');
    }
}
