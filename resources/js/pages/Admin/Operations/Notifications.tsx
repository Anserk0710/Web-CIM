// path: resources/js/pages/Admin/Operations/Notifications.tsx

import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import AdminLayout from '@/layouts/AdminLayout';

type NotificationRow = {
    id: number;
    event_type: string;
    entity_type: string;
    entity_id: number;
    recipient: string | null;
    status: 'PENDING' | 'PROCESSING' | 'SENT' | 'FAILED';
    attempts: number;
    last_error: string | null;
    created_at: string | null;
    last_attempt_at: string | null;
    sent_at: string | null;
};

type Paginator = {
    data: NotificationRow[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type Props = {
    notifications: Paginator;
    filters: {
        status: string;
    };
    canRetry: boolean;
};

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
        },
    ).format(new Date(value));
}

function statusClass(
    status: NotificationRow['status'],
): string {
    if (status === 'FAILED') {
        return 'border-red-300 bg-red-50 text-red-800';
    }

    if (status === 'SENT') {
        return 'border-green-300 bg-green-50 text-green-800';
    }

    if (status === 'PROCESSING') {
        return 'border-blue-300 bg-blue-50 text-blue-800';
    }

    return 'border-amber-300 bg-amber-50 text-amber-900';
}

export default function Notifications({
    notifications,
    filters,
    canRetry,
}: Props) {
    function filterByStatus(
        status: string,
    ): void {
        router.get(
            '/admin/operations/notifications',
            status ? { status } : {},
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    function retry(
        notification: NotificationRow,
    ): void {
        if (
            notification.status !== 'FAILED'
            || !canRetry
        ) {
            return;
        }

        router.post(
            `/admin/operations/notifications/${notification.id}/retry`,
            {},
            {
                preserveScroll: true,
            },
        );
    }

    return (
        <>
            <Head title="Operational Notifications" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Operational Notifications',
                    },
                ]}
            >
                <PageHeader
                    title="Operational Notifications"
                    description="Monitor email delivery, failed attempts, and manual retries."
                />

                <div className="my-6 flex flex-wrap gap-2">
                    {[
                        ['', 'All'],
                        ['PENDING', 'Pending'],
                        ['PROCESSING', 'Processing'],
                        ['SENT', 'Sent'],
                        ['FAILED', 'Failed'],
                    ].map(([value, label]) => (
                        <button
                            key={value}
                            type="button"
                            aria-pressed={
                                filters.status === value
                            }
                            onClick={() =>
                                filterByStatus(value)
                            }
                            className={[
                                'rounded-lg border px-3 py-2 text-sm font-medium',
                                filters.status === value
                                    ? 'border-cim-blue bg-cim-blue text-white'
                                    : 'bg-background hover:bg-muted',
                            ].join(' ')}
                        >
                            {label}
                        </button>
                    ))}
                </div>

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full min-w-[950px] border-collapse text-sm">
                        <thead className="bg-muted/40 text-left">
                            <tr>
                                <th className="p-3">Event</th>
                                <th className="p-3">Entity</th>
                                <th className="p-3">Recipient</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Attempts</th>
                                <th className="p-3">Updated</th>
                                <th className="p-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            {notifications.data.map(
                                (notification) => (
                                    <tr
                                        key={notification.id}
                                        className="border-t align-top"
                                    >
                                        <td className="p-3">
                                            <p className="font-medium">
                                                {notification.event_type}
                                            </p>

                                            <p className="mt-1 text-xs text-muted-foreground">
                                                #{notification.id}
                                            </p>
                                        </td>

                                        <td className="p-3">
                                            {notification.entity_type}
                                            {' #'}
                                            {notification.entity_id}
                                        </td>

                                        <td className="p-3 break-all">
                                            {notification.recipient ?? 'Not configured'}
                                        </td>

                                        <td className="p-3">
                                            <span
                                                className={[
                                                    'inline-flex rounded-full border px-2 py-1 text-xs font-semibold',
                                                    statusClass(
                                                        notification.status,
                                                    ),
                                                ].join(' ')}
                                            >
                                                {notification.status}
                                            </span>

                                            {notification.last_error && (
                                                <p className="mt-2 max-w-xs text-xs text-red-700">
                                                    {notification.last_error}
                                                </p>
                                            )}
                                        </td>

                                        <td className="p-3">
                                            {notification.attempts}
                                        </td>

                                        <td className="p-3">
                                            <p>
                                                {formatDate(
                                                    notification.last_attempt_at,
                                                )}
                                            </p>

                                            {notification.sent_at && (
                                                <p className="mt-1 text-xs text-green-700">
                                                    Sent: {formatDate(
                                                        notification.sent_at,
                                                    )}
                                                </p>
                                            )}
                                        </td>

                                        <td className="p-3">
                                            {canRetry
                                                && notification.status === 'FAILED'
                                                && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            retry(notification)
                                                        }
                                                        className="rounded-lg border border-cim-blue px-3 py-2 text-xs font-semibold text-cim-blue hover:bg-cim-blue hover:text-white"
                                                    >
                                                        Retry
                                                    </button>
                                                )}
                                        </td>
                                    </tr>
                                ),
                            )}

                            {notifications.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-10 text-center text-muted-foreground"
                                    >
                                        No notification records found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <nav
                    aria-label="Notification pagination"
                    className="mt-6 flex items-center justify-between gap-4"
                >
                    <p className="text-sm text-muted-foreground">
                        Page {notifications.current_page}
                        {' of '}
                        {notifications.last_page}
                        {' · '}
                        {notifications.total}
                        {' records'}
                    </p>

                    <div className="flex gap-2">
                        {notifications.prev_page_url ? (
                            <Link
                                href={notifications.prev_page_url}
                                preserveScroll
                                className="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                            >
                                Previous
                            </Link>
                        ) : (
                            <span className="rounded-lg border px-4 py-2 text-sm opacity-50">
                                Previous
                            </span>
                        )}

                        {notifications.next_page_url ? (
                            <Link
                                href={notifications.next_page_url}
                                preserveScroll
                                className="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                            >
                                Next
                            </Link>
                        ) : (
                            <span className="rounded-lg border px-4 py-2 text-sm opacity-50">
                                Next
                            </span>
                        )}
                    </div>
                </nav>
            </AdminLayout>
        </>
    );
}
