<?php

namespace App\Models;

class ActivityCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }
}
