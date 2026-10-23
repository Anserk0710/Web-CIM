// path: resources/js/pages/Admin/Inquiries/Index.tsx

import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    DataTable,
    type DataTableColumn,
} from '@/components/admin/DataTable';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import AdminLayout from '@/layouts/AdminLayout';

type InquiryRow = {
    id: number;
    type: string;
    status: string;
    name: string | null;
    email: string | null;
    organization: string | null;
    created_at: string | null;
    anonymized_at: string | null;
    product_name: string | null;
    catalog_title: string | null;
};

type Filters = {
    q: string;
    type: string;
    status: string;
    sort: string;
    direction: string;
};

type Props = {
    inquiries: {
        data: InquiryRow[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };

    types: string[];
    statuses: string[];
    filters: Filters;
    canExport: boolean;
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

export default function Index({
    inquiries,
    types,
    statuses,
    filters,
    canExport,
}: Props) {
    const columns: DataTableColumn<InquiryRow>[] = [
        {
            key: 'contact',
            header: 'Contact',

            render: (inquiry) => (
                <div>
                    <Link
                        href={`/admin/inquiries/${inquiry.id}`}
                        className="font-semibold text-cim-blue hover:text-cim-navy"
                    >
                        {inquiry.name
                            ?? `Inquiry #${inquiry.id}`}
                    </Link>

                    <p className="mt-1 break-all text-xs text-muted-foreground">
                        {inquiry.email ?? 'Anonymized'}
                    </p>

                    {inquiry.organization && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            {inquiry.organization}
                        </p>
                    )}
                </div>
            ),
        },

        {
            key: 'type',
            header: 'Type',

            render: (inquiry) => inquiry.type,
        },

        {
            key: 'related',
            header: 'Related content',

            render: (inquiry) => (
                <div className="space-y-1">
                    {inquiry.product_name && (
                        <p>
                            Product: {inquiry.product_name}
                        </p>
                    )}

                    {inquiry.catalog_title && (
                        <p>
                            E-Catalog: {inquiry.catalog_title}
                        </p>
                    )}

                    {!inquiry.product_name
                        && !inquiry.catalog_title
                        && '—'}
                </div>
            ),
        },

        {
            key: 'status',
            header: 'Status',

            render: (inquiry) => (
                <span className="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {inquiry.status}
                </span>
            ),
        },

        {
            key: 'created_at',
            header: 'Received',

            render: (inquiry) =>
                formatDate(inquiry.created_at),
        },
    ];

    function submitFilters(
        event: FormEvent<HTMLFormElement>,
    ): void {
        event.preventDefault();

        const data = new FormData(
            event.currentTarget,
        );

        router.get(
            '/admin/inquiries',
            {
                q: String(data.get('q') ?? ''),
                type: String(data.get('type') ?? ''),
                status: String(data.get('status') ?? ''),
                sort: String(data.get('sort') ?? 'created_at'),
                direction: String(data.get('direction') ?? 'desc'),
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    const exportParams = new URLSearchParams();

    Object.entries(filters).forEach(
        ([key, value]) => {
            if (value !== '') {
                exportParams.set(key, value);
            }
        },
    );

    const exportHref =
        `/admin/inquiries/export?${exportParams.toString()}`;

    return (
        <>
            <Head title="Contact Inquiries" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Inquiries',
                    },
                ]}
            >
                <PageHeader
                    title="Contact Inquiries"
                    description="Search, review, and manage public contact inquiries."
                    actions={
                        canExport ? (
                            <a
                                href={exportHref}
                                className="inline-flex h-10 items-center justify-center rounded-lg border bg-background px-4 text-sm font-semibold hover:bg-muted"
                            >
                                Export CSV
                            </a>
                        ) : undefined
                    }
                />

                <form
                    onSubmit={submitFilters}
                    className="my-6 grid gap-3 rounded-xl border bg-background p-4 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_1fr_1fr_auto]"
                >
                    <input
                        name="q"
                        defaultValue={filters.q}
                        placeholder="Search name, email, organization, message..."
                        maxLength={100}
                        className="min-w-0 rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="type"
                        defaultValue={filters.type}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All types
                        </option>

                        {types.map((type) => (
                            <option
                                key={type}
                                value={type}
                            >
                                {type}
                            </option>
                        ))}
                    </select>

                    <select
                        name="status"
                        defaultValue={filters.status}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All statuses
                        </option>

                        {statuses.map((status) => (
                            <option
                                key={status}
                                value={status}
                            >
                                {status}
                            </option>
                        ))}
                    </select>

                    <select
                        name="sort"
                        defaultValue={filters.sort}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="created_at">
                            Received date
                        </option>

                        <option value="name">
                            Name
                        </option>

                        <option value="type">
                            Type
                        </option>

                        <option value="status">
                            Status
                        </option>
                    </select>

                    <select
                        name="direction"
                        defaultValue={filters.direction}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="desc">
                            Descending
                        </option>

                        <option value="asc">
                            Ascending
                        </option>
                    </select>

                    <button
                        type="submit"
                        className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white hover:bg-cim-navy"
                    >
                        Filter
                    </button>
                </form>

                <DataTable
                    rows={inquiries.data}
                    columns={columns}
                    rowKey={(inquiry) => inquiry.id}
                    emptyTitle="No inquiries"
                    emptyDescription="Belum ada inquiry yang sesuai dengan filter."
                />

                {inquiries.last_page > 1 && (
                    <nav
                        aria-label="Inquiry pagination"
                        className="mt-6 flex items-center justify-between gap-4"
                    >
                        <p className="text-sm text-muted-foreground">
                            Page {inquiries.current_page}
                            {' of '}
                            {inquiries.last_page}
                            {' · '}
                            {inquiries.total}
                            {' inquiries'}
                        </p>

                        <div className="flex gap-2">
                            {inquiries.prev_page_url ? (
                                <Link
                                    href={inquiries.prev_page_url}
                                    preserveScroll
                                    className="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                                >
                                    Previous
                                </Link>
                            ) : (
                                <span className="rounded-lg border px-4 py-2 text-sm text-muted-foreground opacity-50">
                                    Previous
                                </span>
                            )}

                            {inquiries.next_page_url ? (
                                <Link
                                    href={inquiries.next_page_url}
                                    preserveScroll
                                    className="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                                >
                                    Next
                                </Link>
                            ) : (
                                <span className="rounded-lg border px-4 py-2 text-sm text-muted-foreground opacity-50">
                                    Next
                                </span>
                            )}
                        </div>
                    </nav>
                )}
            </AdminLayout>
        </>
    );
}
