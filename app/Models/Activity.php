<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends BaseModel
{
    protected $fillable = [
        'public_slug',
        'status',
        'published_at',
        'archived_at',
        'created_by',
        'updated_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(
            ActivityRevision::class,
        );
    }
}
