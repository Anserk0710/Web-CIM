<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ECatalogAvailability;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcatalogEntry extends BaseModel
{
    protected $fillable = [
        'product_id',
        'catalog_code',
        'procurement_title',
        'procurement_category',
        'notes',
        'unit_package',
        'availability',
        'reference_url',
        'effective_at',
        'expires_at',
        'status',
        'sort_order',
        'seo_title',
        'seo_description',
        'published_at',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'availability' => ECatalogAvailability::class,
            'status' => ContentStatus::class,
            'effective_at' => 'datetime',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
