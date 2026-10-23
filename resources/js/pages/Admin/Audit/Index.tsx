import { Head, Link, router } from '@inertiajs/react';
import { DataTable, type DataTableColumn } from '@/components/admin/DataTable';
import { PageHeader } from '@/components/admin/PageHeader';
import AdminLayout from '@/layouts/AdminLayout';
import {
    formatDateTimeIndonesian,
    humanizeAuditResult,
} from '@/lib/humanize';

type Audit = {
    id: number;
    action: string;
    entity_type: string | null;
    entity_id: number | null;
    result: string;
    ip: string | null;
    created_at: string;

    actor: {
        name: string;
        email: string;
    } | null;
};

type Props = {
    logs: {
        data: Audit[];
    };

    filters: {
        action: string;
        entity_type: string;
        result: string;
    };
};

export default function Index({
    logs,
    filters,
}: Props) {
    const columns: DataTableColumn<Audit>[] = [
        {
            key: 'action',
            header: 'Aktivitas / Tindakan',
            render: (audit) => (
                <Link
                    href={`/admin/audit/${audit.id}`}
                    className="font-semibold text-cim-blue hover:text-cim-navy"
                >
                    {audit.action}
                </Link>
            ),
        },
        {
            key: 'actor',
            header: 'Pelaku Tindakan',
            render: (audit) =>
                audit.actor
                    ? audit.actor.name
                    : 'Sistem / Pengunjung Publik',
        },
        {
            key: 'entity',
            header: 'Objek / Data Terkait',
            render: (audit) =>
                audit.entity_type
                    ? `${audit.entity_type} #${audit.entity_id ?? '—'}`
                    : '—',
        },
        {
            key: 'result',
            header: 'Status Hasil',
            render: (audit) => (
                <span className="rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {humanizeAuditResult(audit.result)}
                </span>
            ),
        },
        {
            key: 'created',
            header: 'Waktu Kejadian',
            render: (audit) =>
                formatDateTimeIndonesian(audit.created_at),
        },
    ];

    return (
        <>
            <Head title="Catatan Aktivitas" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Catatan Aktivitas',
                    },
                ]}
            >
                <PageHeader
                    title="Catatan Aktivitas (Audit Log)"
                    description="Riwayat lengkap seluruh tindakan penting dan perubahan data yang tercatat permanen dalam sistem."
                />

                <form
                    className="my-6 grid gap-3 rounded-xl border bg-background p-4 md:grid-cols-4"
                    onSubmit={(event) => {
                        event.preventDefault();

                        const form = new FormData(
                            event.currentTarget,
                        );

                        router.get(
                            '/admin/audit',
                            {
                                action: String(
                                    form.get(
                                        'action',
                                    )
                                    ?? '',
                                ),

                                entity_type:
                                    String(
                                        form.get(
                                            'entity_type',
                                        )
                                        ?? '',
                                    ),

                                result: String(
                                    form.get(
                                        'result',
                                    )
                                    ?? '',
                                ),
                            },
                            {
                                preserveState: true,
                                replace: true,
                            },
                        );
                    }}
                >
                    <input
                        name="action"
                        defaultValue={
                            filters.action
                        }
                        placeholder="Cari jenis tindakan..."
                        className="rounded-lg border border-input px-3 py-2"
                    />

                    <input
                        name="entity_type"
                        defaultValue={
                            filters.entity_type
                        }
                        placeholder="Jenis data (misal: Product, User)..."
                        className="rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="result"
                        defaultValue={
                            filters.result
                        }
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            Semua Status Hasil
                        </option>

                        <option value="SUCCESS">
                            Berhasil (SUCCESS)
                        </option>

                        <option value="DENIED">
                            Akses Ditolak (DENIED)
                        </option>

                        <option value="FAILURE">
                            Gagal (FAILURE)
                        </option>
                    </select>

                    <button
                        type="submit"
                        className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white hover:bg-cim-navy"
                    >
                        Saring Data
                    </button>
                </form>

                <DataTable
                    rows={logs.data}
                    columns={columns}
                    rowKey={(audit) =>
                        audit.id
                    }
                    emptyTitle="Belum Ada Catatan Aktivitas"
                    emptyDescription="Setiap tindakan penting dan perubahan data akan tercatat secara permanen di sini."
                />
            </AdminLayout>
        </>
    );
}