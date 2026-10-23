<?php

// path: app/Models/InquiryNote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryNote extends BaseModel
{
    protected $fillable = [
        'inquiry_id',
        'author_id',
        'body',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(
            Inquiry::class,
            'inquiry_id',
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'author_id',
        );
    }
}
