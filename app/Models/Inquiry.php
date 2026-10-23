<?php

// path: app/Models/Inquiry.php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ecatalogEntry(): BelongsTo
    {
        return $this->belongsTo(
            EcatalogEntry::class,
            'ecatalog_entry_id',
        );
    }

    public function notes(): HasMany
    {
        return $this->hasMany(
            InquiryNote::class,
            'inquiry_id',
        )
            ->latest('created_at')
            ->latest('id');
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }
}
