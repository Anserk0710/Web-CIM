<?php

namespace App\Models;

use App\Enums\RevisionKind;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(
            ProductRevisionMedia::class,
            'revision_id',
        )->orderBy('sort_order');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(ProductHighlight::class, 'revision_id')->orderBy('sort_order');
    }

    public function specificationGroups(): HasMany
    {
        return $this->hasMany(SpecificationGroup::class, 'revision_id')->orderBy('sort_order');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_revision_related',
            'revision_id',
            'related_product_id',
        )
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
