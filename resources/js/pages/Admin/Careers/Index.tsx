import { Head, Link, router } from '@inertiajs/react';
import { DataTable, type DataTableColumn } from '@/components/admin/DataTable';
import { PageHeader } from '@/components/admin/PageHeader';
import { humanizeStatus } from '@/lib/humanize';
import AdminLayout from '@/layouts/AdminLayout';

type Career = {
    id: number;
    title: string;
    department: string;
    location: string;
    employment_type: string;
    status: string;
};

type Props = {
    careers: {
        data: Career[];
    };

    filters: {
        q: string;
        status: string;
    };

    canCreate: boolean;
};

export default function Index({
    careers,
    filters,
    canCreate,
}: Props) {
    const columns: DataTableColumn<Career>[] = [
        {
            key: 'title',
            header: 'Posisi Lowongan',
            render: (career) => (
                <Link
                    href={`/admin/careers/${career.id}/edit`}
                    className="font-semibold text-cim-blue hover:text-cim-navy"
                >
                    {career.title}
                </Link>
            ),
        },

        {
            key: 'department',
            header: 'Departemen',
            render: (career) => career.department
        },
        {
            key: 'location',
            header: 'Lokasi Penempatan',
            render: (career) => career.location
        },
        {
            key: 'status',
            header: 'Status Lowongan',
            render: (career) => (
                <span className="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold">
                    {humanizeStatus(career.status)}
                </span>
            )
        },
    ];

    return (
        <>
            <Head title="Karir & Lowongan Pekerjaan" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Karir & Lowongan',
                    },
                ]}
            >
                <PageHeader
                    title="Lowongan Pekerjaan"
                    description="Kelola pengumuman posisi karir, persyaratan kualifikasi, dan masa aktif pendaftaran."
                    actions={
                        canCreate ? (
                            <Link
                                href="/admin/careers/create"
                                className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white hover:bg-cim-navy"
                            >
                                Tambah Lowongan Baru
                            </Link>
                        ) : undefined
                    }
                />

                <form
                    className="my-6 flex flex-col gap-3 rounded-xl border bg-background p-4 sm:flex-row"
                    onSubmit={(event) => {
                        event.preventDefault();

                        const form = new FormData(
                            event.currentTarget,
                        );

                        router.get(
                            '/admin/careers',
                            {
                                q: String(
                                    form.get('q')
                                    ?? '',
                                ),

                                status: String(
                                    form.get('status')
                                    ?? '',
                                ),
                            },
                            {
                                replace: true,
                                preserveState: true,
                            },
                        );
                    }}
                >
                    <input
                        name="q"
                        defaultValue={filters.q}
                        placeholder="Cari posisi, departemen, atau lokasi..."
                        className="flex-1 rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="status"
                        defaultValue={filters.status}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        {[
                            'DRAFT',
                            'OPEN',
                            'CLOSED',
                            'ARCHIVED',
                        ].map((status) => (
                            <option
                                key={status}
                                value={status}
                            >
                                {humanizeStatus(status)}
                            </option>
                        ))}
                    </select>

                    <button className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white hover:bg-cim-navy">
                        Terapkan Filter
                    </button>
                </form>

                <DataTable
                    rows={careers.data}
                    columns={columns}
                    rowKey={(career) =>
                        career.id
                    }
                    emptyTitle="Belum Ada Lowongan Pekerjaan"
                    emptyDescription="Silakan tambahkan informasi lowongan pekerjaan pertama Anda."
                />
            </AdminLayout>
        </>
    );
}