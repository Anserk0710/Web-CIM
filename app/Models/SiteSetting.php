<?php

namespace App\Models;

class SiteSetting extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'updated_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'lock_version' => 'integer',
        ];
    }
}
