<?php

namespace App\Models;

use App\Enums\RevisionKind;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityRevision extends BaseModel
{
    protected $fillable = [
        'activity_id',
        'kind',
        'requested_slug',
        'title',
        'excerpt',
        'category_id',
        'featured_media_id',
        'is_featured',
        'seo_title',
        'seo_description',
        'og_media_id',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'kind' => RevisionKind::class,
            'is_featured' => 'boolean',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(
            ActivityBlock::class,
            'revision_id',
        );
    }
}
