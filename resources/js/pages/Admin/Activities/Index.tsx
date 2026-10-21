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

type Activity = {
    id: number;
    status: string;

    working_revision: {
        title: string;
        requested_slug: string;
    } | null;
};

type Props = {
    activities: {
        data: Activity[];
    };

    filters: {
        q: string;
        status: string;
    };

    canCreate: boolean;
};

export default function Index({
    activities,
    filters,
    canCreate,
}: Props) {
    const columns: DataTableColumn<Activity>[] = [
        {
            key: 'title',
            header: 'Activity',
            render: (activity) => (
                <Link
                    href={`/admin/activities/${activity.id}/edit`}
                    className="font-semibold text-cim-blue"
                >
                    {activity
                        .working_revision
                        ?.title
                        ?? `#${activity.id}`}
                </Link>
            ),
        },
        {
            key: 'slug',
            header: 'Requested Slug',
            render: (activity) =>
                activity
                    .working_revision
                    ?.requested_slug
                ?? '—',
        },
        {
            key: 'status',
            header: 'Status',
            render: (activity) =>
                activity.status,
        },
    ];

    return (
        <>
            <Head title="Activities" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Activities',
                    },
                ]}
            >
                <PageHeader
                    title="Activities"
                    description="Structured block content dengan WORKING/PUBLISHED revision."
                    actions={(
                        <div className="flex gap-2">
                            <Link
                                href="/admin/activities/categories"
                                className="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium"
                            >
                                Categories
                            </Link>

                            {canCreate && (
                                <Link
                                    href="/admin/activities/create"
                                    className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white"
                                >
                                    Create Activity
                                </Link>
                            )}
                        </div>
                    )}
                />

                <form
                    className="my-6 flex flex-col gap-3 rounded-xl border bg-background p-4 sm:flex-row"
                    onSubmit={(event) => {
                        event.preventDefault();

                        const form = new FormData(
                            event.currentTarget,
                        );

                        router.get(
                            '/admin/activities',
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
                        placeholder="Search title..."
                        className="flex-1 rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="status"
                        defaultValue={filters.status}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All
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
                    rows={activities.data}
                    columns={columns}
                    rowKey={(activity) =>
                        activity.id
                    }
                    emptyTitle="Belum ada Activity"
                    emptyDescription="Buat Activity pertama."
                />
            </AdminLayout>
        </>
    );
}