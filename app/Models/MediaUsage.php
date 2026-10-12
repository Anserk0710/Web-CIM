<?php

namespace App\Models;

class MediaUsage extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'owner_type',
        'owner_id',
        'slot',
    ];
}
