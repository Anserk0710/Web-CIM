<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends BaseModel
{
    protected $fillable = [
        'page_id',
        'key',
        'data',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(
            SitePage::class,
            'page_id',
        );
    }
}
