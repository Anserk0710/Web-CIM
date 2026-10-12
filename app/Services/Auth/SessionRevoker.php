<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionRevoker
{
    public function revokeAll(User $user): void
    {
        if (
            config('session.driver')
            !== 'database'
        ) {
            return;
        }

        DB::table(
            config('session.table', 'sessions'),
        )
            ->where('user_id', $user->id)
            ->delete();
    }

    public function revokeAllExcept(
        User $user,
        ?string $sessionId,
    ): void {
        if (
            config('session.driver')
            !== 'database'
        ) {
            return;
        }

        $query = DB::table(
            config('session.table', 'sessions'),
        )->where(
            'user_id',
            $user->id,
        );

        if ($sessionId !== null) {
            $query->where(
                'id',
                '!=',
                $sessionId,
            );
        }

        $query->delete();
    }
}
