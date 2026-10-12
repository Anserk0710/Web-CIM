<?php

namespace App\Models;

use App\Enums\AuditResult;

class AuditLog extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'result',
        'changes',
        'request_id',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => AuditResult::class,
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
