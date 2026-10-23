import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    DataTable,
    type DataTableColumn,
} from '@/components/admin/DataTable';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import AdminLayout from '@/layouts/AdminLayout';
import {
    humanizeAvailability,
    humanizeStatus,
} from '@/lib/humanize';

type Entry = {
    id: number;
    catalog_code: string;
    procurement_title: string;
    availability: string;
    status: string;
    updated_at: string;

    product: {
        id: number;

        published_revision: {
            name: string;
        } | null;
    };
};

type Props = {
    entries: {
        data: Entry[];
    };

    filters: {
        q: string;
        status: string;
    };

    can: {
        create: boolean;
    };
};

export default function Index({
    entries,
    filters,
    can,
}: Props) {
    const columns: DataTableColumn<Entry>[] = [
        {
            key: 'code',
            header: 'Kode Katalog',
            render: (entry) => (
                <Link
                    href={`/admin/ecatalog/${entry.id}/edit`}
                    className="font-semibold text-cim-blue hover:text-cim-navy"
                >
                    {entry.catalog_code}
                </Link>
            ),
        },
        {
            key: 'title',
            header: 'Nama Pengadaan & Produk',
            render: (entry) => (
                <div>
                    <p className="font-medium text-cim-navy">
                        {entry.procurement_title}
                    </p>

                    <p className="mt-1 text-xs text-muted-foreground">
                        {entry.product
                            .published_revision
                            ?.name
                            ?? `Produk #${entry.product.id}`}
                    </p>
                </div>
            ),
        },
        {
            key: 'availability',
            header: 'Ketersediaan Stok',
            render: (entry) => (
                <span className="rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {humanizeAvailability(entry.availability)}
                </span>
            ),
        },
        {
            key: 'status',
            header: 'Status Publikasi',
            render: (entry) => (
                <span className="rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {humanizeStatus(entry.status)}
                </span>
            ),
        },
    ];

    return (
        <>
            <Head title="E-Katalog" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'E-Katalog',
                    },
                ]}
            >
                <PageHeader
                    title="E-Katalog Pengadaan"
                    description="Kelola data e-katalog LKPP yang terhubung langsung dengan produk alat kesehatan."
                    actions={
                        can.create ? (
                            <Link
                                href="/admin/ecatalog/create"
                                className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white hover:bg-cim-navy"
                            >
                                Tambah E-Katalog
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
                            '/admin/ecatalog',
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
                                preserveState: true,
                                replace: true,
                            },
                        );
                    }}
                >
                    <input
                        name="q"
                        defaultValue={filters.q}
                        placeholder="Cari kode katalog atau nama pengadaan..."
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

                        <option value="DRAFT">
                            Draf
                        </option>

                        <option value="PUBLISHED">
                            Diterbitkan
                        </option>

                        <option value="ARCHIVED">
                            Diarsipkan
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
                    rows={entries.data}
                    columns={columns}
                    rowKey={(entry) => entry.id}
                    emptyTitle="Belum Ada Data E-Katalog"
                    emptyDescription="Belum ada data katalog pengadaan yang tercatat dalam sistem."
                />
            </AdminLayout>
        </>
    );
}