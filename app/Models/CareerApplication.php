<?php

namespace App\Models;

use App\Enums\ApplicationStatus;

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
}
