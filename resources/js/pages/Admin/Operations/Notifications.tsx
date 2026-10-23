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
import {
    formatIndonesianDate,
    humanizeEntity,
    humanizeEvent,
    humanizeStatus,
} from '@/lib/humanize';

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
            <Head title="Pengiriman Notifikasi Email" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pengiriman Notifikasi',
                    },
                ]}
            >
                <PageHeader
                    title="Pengiriman Notifikasi & Email"
                    description="Pantau status antrean pengiriman email sistem, percobaan pengiriman, dan kirim ulang jika ada kendala."
                />

                <div className="my-6 flex flex-wrap gap-2">
                    {[
                        ['', 'Semua'],
                        ['PENDING', 'Menunggu Antrean'],
                        ['PROCESSING', 'Sedang Dikirim'],
                        ['SENT', 'Berhasil Terkirim'],
                        ['FAILED', 'Gagal Dikirim'],
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
                                'rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
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
                                <th className="p-3">Peristiwa / Notifikasi</th>
                                <th className="p-3">Data Terkait</th>
                                <th className="p-3">Penerima Email</th>
                                <th className="p-3">Status Pengiriman</th>
                                <th className="p-3">Percobaan</th>
                                <th className="p-3">Terakhir Diperbarui</th>
                                <th className="p-3">Tindakan</th>
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
                                            <p className="font-medium text-cim-navy">
                                                {humanizeEvent(notification.event_type)}
                                            </p>

                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Nomor antrean: #{notification.id}
                                            </p>
                                        </td>

                                        <td className="p-3">
                                            {humanizeEntity(notification.entity_type)}
                                            {' #'}
                                            {notification.entity_id}
                                        </td>

                                        <td className="p-3 break-all font-mono text-xs">
                                            {notification.recipient ?? 'Alamat email belum diatur'}
                                        </td>

                                        <td className="p-3">
                                            <span
                                                className={[
                                                    'inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold',
                                                    statusClass(
                                                        notification.status,
                                                    ),
                                                ].join(' ')}
                                            >
                                                {humanizeStatus(notification.status)}
                                            </span>

                                            {notification.last_error && (
                                                <p className="mt-2 max-w-xs text-xs text-red-700">
                                                    Kendala: {notification.last_error}
                                                </p>
                                            )}
                                        </td>

                                        <td className="p-3">
                                            {notification.attempts} kali
                                        </td>

                                        <td className="p-3">
                                            <p>
                                                {formatIndonesianDate(
                                                    notification.last_attempt_at,
                                                )}
                                            </p>

                                            {notification.sent_at && (
                                                <p className="mt-1 text-xs text-green-700">
                                                    Terkirim: {formatIndonesianDate(
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
                                                        className="rounded-lg border border-cim-blue px-3 py-2 text-xs font-semibold text-cim-blue hover:bg-cim-blue hover:text-white transition-colors"
                                                    >
                                                        Coba Kirim Ulang
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
                                        Belum ada riwayat antrean pengiriman notifikasi yang tercatat.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <nav
                    aria-label="Navigasi halaman notifikasi"
                    className="mt-6 flex items-center justify-between gap-4"
                >
                    <p className="text-sm text-muted-foreground">
                        Halaman {notifications.current_page}
                        {' dari '}
                        {notifications.last_page}
                        {' · Total '}
                        {notifications.total}
                        {' data'}
                    </p>

                    <div className="flex gap-2">
                        {notifications.prev_page_url ? (
                            <Link
                                href={notifications.prev_page_url}
                                preserveScroll
                                className="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                            >
                                Sebelumnya
                            </Link>
                        ) : (
                            <span className="rounded-lg border px-4 py-2 text-sm opacity-50">
                                Sebelumnya
                            </span>
                        )}

                        {notifications.next_page_url ? (
                            <Link
                                href={notifications.next_page_url}
                                preserveScroll
                                className="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                            >
                                Selanjutnya
                            </Link>
                        ) : (
                            <span className="rounded-lg border px-4 py-2 text-sm opacity-50">
                                Selanjutnya
                            </span>
                        )}
                    </div>
                </nav>
            </AdminLayout>
        </>
    );
}

