<?php

namespace App\Models;

use App\Enums\CareerStatus;
use App\Enums\EmploymentType;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Career extends BaseModel
{
    protected $fillable = [
        'slug',
        'title',
        'department',
        'location',
        'employment_type',
        'summary',
        'responsibilities',
        'requirements',
        'status',
        'opens_at',
        'closes_at',
        'closed_at',
        'seo_title',
        'seo_description',
        'created_by',
        'updated_by',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'status' => CareerStatus::class,
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'closed_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            CareerApplication::class,
        );
    }
}
