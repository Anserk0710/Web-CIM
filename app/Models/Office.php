<?php

namespace App\Models;

class Office extends BaseModel
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'hours',
        'is_head_office',
        'sort_order',
        'is_active',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'hours' => 'array',
            'is_head_office' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }
}
