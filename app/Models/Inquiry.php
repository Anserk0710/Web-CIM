<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;

class Inquiry extends BaseModel
{
    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'organization',
        'message',
        'product_id',
        'ecatalog_entry_id',
        'status',
        'resolved_at',
        'spam_marked_at',
        'privacy_notice_version',
        'anonymized_at',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'type' => InquiryType::class,
            'status' => InquiryStatus::class,
            'resolved_at' => 'datetime',
            'spam_marked_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }
}
