<?php // path: app/Http/Controllers/Admin/NotificationOutboxController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationOutbox;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NotificationOutboxController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize('notification.read');

        $status = strtoupper(
            trim(
                (string) $request->query(
                    'status',
                    '',
                ),
            ),
        );

        $allowedStatuses = [
            NotificationOutbox::PENDING,
            NotificationOutbox::PROCESSING,
            NotificationOutbox::SENT,
            NotificationOutbox::FAILED,
        ];

        $query = NotificationOutbox::query();

        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        } else {
            $status = '';
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString()
            ->through(
                fn (
                    NotificationOutbox $record,
                ): array => [
                    'id' => $record->id,
                    'event_type' => $record->event_type,
                    'entity_type' => $record->entity_type,
                    'entity_id' => $record->entity_id,
                    'recipient' => $record->recipient,
                    'status' => $record->status,
                    'attempts' => $record->attempts,
                    'last_error' => $record->last_error,
                    'created_at' => $record->created_at
                        ?->toIso8601String(),
                    'last_attempt_at' => $record->last_attempt_at
                        ?->toIso8601String(),
                    'sent_at' => $record->sent_at
                        ?->toIso8601String(),
                ],
            );

        return Inertia::render(
            'Admin/Operations/Notifications',
            [
                'notifications' => $notifications,
                'filters' => [
                    'status' => $status,
                ],
                'canRetry' => $request
                    ->user()
                    ->can('notification.retry'),
            ],
        );
    }

    public function retry(
        Request $request,
        NotificationOutbox $notification,
        NotificationOutboxService $service,
    ): RedirectResponse {
        Gate::authorize('notification.retry');

        if (
            $notification->status
            !== NotificationOutbox::FAILED
        ) {
            return back()->with(
                'status',
                'Hanya notifikasi gagal yang dapat di-retry.',
            );
        }

        $retried = $service->retryFailed(
            $notification,
            $request->user(),
        );

        if (! $retried) {
            return back()->with(
                'status',
                'Status notifikasi berubah. Muat ulang daftar sebelum mencoba lagi.',
            );
        }

        return back()->with(
            'success',
            'Notifikasi dijadwalkan ulang. Periksa statusnya setelah worker memproses antrean.',
        );
    }
}
