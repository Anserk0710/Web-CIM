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
    humanizeStatus,
} from '@/lib/humanize';

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
            header: 'Judul Berita & Kegiatan',
            render: (activity) => (
                <Link
                    href={`/admin/activities/${activity.id}/edit`}
                    className="font-semibold text-cim-blue hover:text-cim-navy"
                >
                    {activity
                        .working_revision
                        ?.title
                        ?? `Artikel #${activity.id}`}
                </Link>
            ),
        },
        {
            key: 'slug',
            header: 'Tautan Slug',
            render: (activity) =>
                activity
                    .working_revision
                    ?.requested_slug
                ?? '—',
        },
        {
            key: 'status',
            header: 'Status Publikasi',
            render: (activity) => (
                <span className="rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {humanizeStatus(activity.status)}
                </span>
            ),
        },
    ];

    return (
        <>
            <Head title="Berita & Kegiatan" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Berita & Kegiatan',
                    },
                ]}
            >
                <PageHeader
                    title="Berita & Kegiatan"
                    description="Kelola publikasi berita, artikel informatif, dan dokumentasi kegiatan PT Cahya Intan Medika."
                    actions={(
                        <div className="flex gap-2">
                            <Link
                                href="/admin/activities/categories"
                                className="inline-flex h-10 items-center rounded-lg border bg-background px-4 text-sm font-medium hover:bg-muted"
                            >
                                Kategori Berita
                            </Link>

                            {canCreate && (
                                <Link
                                    href="/admin/activities/create"
                                    className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white hover:bg-cim-navy"
                                >
                                    Tulis Berita Baru
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
                        placeholder="Cari judul berita atau kegiatan..."
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
                    rows={activities.data}
                    columns={columns}
                    rowKey={(activity) =>
                        activity.id
                    }
                    emptyTitle="Belum Ada Berita & Kegiatan"
                    emptyDescription="Belum ada artikel atau dokumentasi kegiatan yang dibuat."
                />
            </AdminLayout>
        </>
    );
}