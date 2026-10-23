<?php

namespace App\Models;

use App\Enums\CareerStatus;
use App\Enums\EmploymentType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public function scopeOpen(
        Builder $query,
    ): Builder {
        return $query->where(
            'status',
            CareerStatus::Open->value,
        );
    }

    public function isAcceptingApplicationsAt(
        ?CarbonInterface $at = null,
    ): bool {
        $at ??= Carbon::now();

        if ($this->status !== CareerStatus::Open) {
            return false;
        }

        if (
            $this->opens_at !== null
            && $this->opens_at->greaterThan($at)
        ) {
            return false;
        }

        if (
            $this->closes_at !== null
            && ! $at->lessThan($this->closes_at)
        ) {
            return false;
        }

        return true;
    }
}
