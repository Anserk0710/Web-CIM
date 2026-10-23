<?php // path: app/Console/Commands/DispatchPendingNotifications.php

namespace App\Console\Commands;

use App\Models\NotificationOutbox;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Console\Command;

class DispatchPendingNotifications extends Command
{
    protected $signature = 'notifications:dispatch-pending';

    protected $description = 'Dispatch due pending CIM notification outbox records.';

    public function handle(
        NotificationOutboxService $service,
    ): int {
        $batchSize = (int) config(
            'notifications.dispatch_batch_size',
            100,
        );

        $staleQueuedBefore = now()->subMinutes(
            (int) config(
                'notifications.queued_timeout_minutes',
                10,
            ),
        );

        $records = NotificationOutbox::query()
            ->where(
                'status',
                NotificationOutbox::PENDING,
            )
            ->where(function ($query): void {
                $query
                    ->whereNull('next_attempt_at')
                    ->orWhere(
                        'next_attempt_at',
                        '<=',
                        now(),
                    );
            })
            ->where(function ($query) use (
                $staleQueuedBefore,
            ): void {
                $query
                    ->whereNull('queued_at')
                    ->orWhere(
                        'queued_at',
                        '<=',
                        $staleQueuedBefore,
                    );
            })
            ->orderBy('id')
            ->limit($batchSize)
            ->get(['id']);

        $dispatched = 0;

        foreach ($records as $record) {
            if ($service->dispatchIfDue(
                (int) $record->id,
            )) {
                $dispatched++;
            }
        }

        $this->info(
            'Notification jobs dispatched: '.$dispatched,
        );

        return self::SUCCESS;
    }
}
