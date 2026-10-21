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
            header: 'Code',
            render: (entry) => (
                <Link
                    href={`/admin/ecatalog/${entry.id}/edit`}
                    className="font-semibold text-cim-blue"
                >
                    {entry.catalog_code}
                </Link>
            ),
        },
        {
            key: 'title',
            header: 'Procurement',
            render: (entry) => (
                <div>
                    <p className="font-medium">
                        {entry.procurement_title}
                    </p>

                    <p className="mt-1 text-xs text-muted-foreground">
                        {entry.product
                            .published_revision
                            ?.name
                            ?? `Product #${entry.product.id}`}
                    </p>
                </div>
            ),
        },
        {
            key: 'availability',
            header: 'Availability',
            render: (entry) =>
                entry.availability,
        },
        {
            key: 'status',
            header: 'Status',
            render: (entry) =>
                entry.status,
        },
    ];

    return (
        <>
            <Head title="E-Catalog" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'E-Catalog',
                    },
                ]}
            >
                <PageHeader
                    title="E-Catalog"
                    description="Procurement records terhubung ke satu Product."
                    actions={
                        can.create ? (
                            <Link
                                href="/admin/ecatalog/create"
                                className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white"
                            >
                                Create E-Catalog
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
                        placeholder="Search..."
                        className="flex-1 rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="status"
                        defaultValue={filters.status}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All status
                        </option>

                        <option value="DRAFT">
                            DRAFT
                        </option>

                        <option value="PUBLISHED">
                            PUBLISHED
                        </option>

                        <option value="ARCHIVED">
                            ARCHIVED
                        </option>
                    </select>

                    <button className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white">
                        Filter
                    </button>
                </form>

                <DataTable
                    rows={entries.data}
                    columns={columns}
                    rowKey={(entry) => entry.id}
                    emptyTitle="Belum ada E-Catalog"
                    emptyDescription="Buat record procurement pertama."
                />
            </AdminLayout>
        </>
    );
}