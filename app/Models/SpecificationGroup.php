<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecificationGroup extends BaseModel
{
    protected $table = 'spec_groups';

    protected $fillable = [
        'revision_id',
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(
            ProductRevision::class,
            'revision_id',
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SpecificationItem::class,
            'group_id',
        )->orderBy('sort_order');
    }
}
