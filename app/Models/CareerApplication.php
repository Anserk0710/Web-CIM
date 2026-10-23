<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerApplication extends BaseModel
{
    protected $fillable = [
        'career_id',
        'full_name',
        'email',
        'email_normalized',
        'phone',
        'introduction',
        'portfolio_url',
        'linkedin_url',
        'cv_disk',
        'cv_path',
        'cv_original_name',
        'cv_mime',
        'cv_size',
        'cv_sha256',
        'status',
        'consent_version_id',
        'consented_at',
        'selection_completed_at',
        'retention_due_at',
        'hr_handover_at',
        'anonymized_at',
        'lock_version',
    ];

    protected $hidden = [
        'cv_disk',
        'cv_path',
        'cv_sha256',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'cv_size' => 'integer',
            'consented_at' => 'datetime',
            'selection_completed_at' => 'datetime',
            'retention_due_at' => 'datetime',
            'hr_handover_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(
            Career::class,
        );
    }

    public function consentVersion(): BelongsTo
    {
        return $this->belongsTo(
            ConsentVersion::class,
            'consent_version_id',
        );
    }

    public function notes(): HasMany
    {
        return $this->hasMany(
            ApplicationNote::class,
            'application_id',
        )->latest('id');
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(
            ApplicationStatusEvent::class,
            'application_id',
        )
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }
}
