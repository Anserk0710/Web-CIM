<?php

namespace App\Models;

use App\Support\Rbac\PermissionRegistry;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
        'remember_token',
        'lock_version',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::lower(
                trim($value),
            ),
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_role',
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()
            ->where(
                'roles.name',
                Role::SUPER_ADMIN,
            )
            ->exists();
    }

    public function hasPermission(
        string $permission,
    ): bool {
        if (! $this->is_active) {
            return false;
        }

        if (
            ! in_array(
                $permission,
                PermissionRegistry::all(),
                true,
            )
        ) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas(
                'permissions',
                fn ($query) => $query->where(
                    'permissions.name',
                    $permission,
                ),
            )
            ->exists();
    }

    public function hasAnyPermission(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions')
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function effectivePermissions(): array
    {
        if (! $this->is_active) {
            return [];
        }

        if ($this->isSuperAdmin()) {
            return PermissionRegistry::all();
        }

        return $this->roles()
            ->with('permissions:id,name')
            ->get()
            ->flatMap(
                fn (Role $role) => $role->permissions
                    ->pluck('name'),
            )
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
