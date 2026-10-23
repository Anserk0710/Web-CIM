<?php // path: app/Models/NotificationOutbox.php

namespace App\Models;

class NotificationOutbox extends BaseModel
{
    public const PENDING = 'PENDING';

    public const PROCESSING = 'PROCESSING';

    public const SENT = 'SENT';

    public const FAILED = 'FAILED';

    protected $table = 'notification_outbox';

    protected $fillable = [
        'event_key',
        'event_type',
        'entity_type',
        'entity_id',
        'recipient',
        'payload',
        'status',
        'attempts',
        'queued_at',
        'processing_at',
        'next_attempt_at',
        'last_attempt_at',
        'sent_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'queued_at' => 'datetime',
            'processing_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
