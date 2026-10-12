<?php

namespace App\Models;

class ProductCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }
}
