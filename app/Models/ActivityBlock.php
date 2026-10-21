<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityBlock extends BaseModel
{
    protected $fillable = [
        'revision_id',
        'block_key',
        'type',
        'data',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityRevision::class,
            'revision_id',
        );
    }
}
