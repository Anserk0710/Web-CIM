<?php

namespace App\Models;

use App\Enums\RevisionKind;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ActivityCategory::class,
            'category_id',
        );
    }

    public function featureMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'featured_media_id',
        );
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(
            Media::class,
            'og_media_id',
        );
    }

    /**
     * @return HasMany<ActivityBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(
            ActivityBlock::class,
            'revision_id',
        );
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'activity_revision_related',
            'revision_id',
            'product_id',
        )
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
