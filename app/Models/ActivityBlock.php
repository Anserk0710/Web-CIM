<?php

namespace App\Models;

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
}
