<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationNote extends BaseModel
{
    protected $fillable = [
        'application_id',
        'author_id',
        'body',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            CareerApplication::class,
            'application_id',
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'author_id',
        );
    }
}
