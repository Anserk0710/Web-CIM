<?php

namespace App\Models;

class AccessGuard extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];
}
