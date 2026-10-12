<?php

namespace App\Models;

use App\Enums\RevisionKind;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRevision extends BaseModel
{
    protected $fillable = [
        'product_id',
        'kind',
        'requested_slug',
        'name',
        'summary',
        'description',
        'category_id',
        'brand_id',
        'type_id',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(
            ProductRevisionMedia::class,
            'revision_id',
        );
    }

    public function specificationGroups(): HasMany
    {
        return $this->hasMany(
            SpecificationGroup::class,
            'revision_id',
        );
    }
}
