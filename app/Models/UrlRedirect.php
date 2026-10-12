<?php

namespace App\Models;

class UrlRedirect extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'old_path',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
