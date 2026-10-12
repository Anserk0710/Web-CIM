<?php

namespace App\Models;

use App\Enums\ProductMediaRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRevisionMedia extends BaseModel
{
    protected $table = 'product_revision_media';

    protected $fillable = [
        'revision_id',
        'media_id',
        'role',
        'sort_order',
        'alt_text',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'role' => ProductMediaRole::class,
            'sort_order' => 'integer',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(
            ProductRevision::class,
            'revision_id',
        );
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
