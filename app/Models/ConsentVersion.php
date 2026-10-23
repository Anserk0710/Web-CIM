<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ConsentVersion extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'version',
        'text',
        'purpose',
        'effective_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function scopeEffective(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'effective_at',
                '<=',
                now(),
            )
            ->orderByDesc('effective_at')
            ->orderByDesc('id');
    }
}
