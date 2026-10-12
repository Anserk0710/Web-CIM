<?php

namespace App\Models;

class SpecificationItem extends BaseModel
{
    protected $table = 'spec_items';

    protected $fillable = [
        'group_id',
        'label',
        'value',
        'unit',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
