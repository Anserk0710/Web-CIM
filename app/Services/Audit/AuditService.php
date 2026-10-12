<?php

namespace App\Services\Audit;

use App\Enums\AuditResult;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    private const FORBIDDEN_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'remember_token',
        'cv_path',
        'cv_sha256',
        'cv_disk',
    ];

    /**
     * @param  array<string, mixed>  $changes
     */
    public function record(
        string $action,
        ?Model $entity = null,
        AuditResult $result = AuditResult::Success,
        array $changes = [],
        ?User $actor = null,
    ): AuditLog {
        $request = app()->bound('request')
            ? request()
            : null;

        return AuditLog::query()->create([
            'actor_id' => $actor?->id
                ?? $request?->user()?->id,

            'action' => $action,

            'entity_type' => $entity === null
                ? null
                : class_basename($entity),

            'entity_id' => $entity?->getKey(),

            'result' => $result,

            'changes' => $changes === []
                ? null
                : $this->sanitize($changes),

            'request_id' => $this->requestId(
                $request,
            ),

            'ip' => $request?->ip(),

            'user_agent' => $request === null
                ? null
                : mb_substr(
                    (string) $request->userAgent(),
                    0,
                    500,
                ),

            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function sanitize(
        array $changes,
    ): array {
        $safe = [];

        foreach ($changes as $key => $value) {
            if ($this->isForbiddenKey((string) $key)) {
                continue;
            }

            if (is_array($value)) {
                $safe[$key] = $this->sanitize(
                    $value,
                );

                continue;
            }

            $safe[$key] = $value;
        }

        return $safe;
    }

    private function isForbiddenKey(
        string $key,
    ): bool {
        $normalized = mb_strtolower($key);

        foreach (
            self::FORBIDDEN_KEYS as $forbidden
        ) {
            if (
                str_contains(
                    $normalized,
                    $forbidden,
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function requestId(
        ?Request $request,
    ): ?string {
        if ($request === null) {
            return null;
        }

        return $request->attributes->get(
            'request_id',
        );
    }
}
