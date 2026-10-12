<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    public const SUPER_ADMIN = 'Super Admin';

    protected $fillable = [
        'name',
        'description',
        'is_system',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_role',
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
        );
    }

    public function isSuperAdminRole(): bool
    {
        return $this->name === self::SUPER_ADMIN;
    }
}
