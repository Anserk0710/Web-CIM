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

type CareerOption = {
    id: number;
    title: string;
};

type ApplicationRow = {
    id: number;
    full_name: string | null;
    email: string | null;
    status: string;
    created_at: string | null;
    anonymized_at: string | null;

    career: {
        id: number;
        title: string;
        slug: string;
    };
};

type Props = {
    applications: {
        data: ApplicationRow[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };

    careers: CareerOption[];
    statuses: string[];

    filters: {
        q: string;
        status: string;
        career_id: number | null;
    };
};

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
        },
    ).format(
        new Date(value),
    );
}

export default function Index({
    applications,
    careers,
    statuses,
    filters,
}: Props) {
    const columns: DataTableColumn<ApplicationRow>[] = [
        {
            key: 'applicant',
            header: 'Applicant',

            render: (application) => (
                <div>
                    <Link
                        href={`/admin/applications/${application.id}`}
                        className="font-semibold text-cim-blue hover:text-cim-navy"
                    >
                        {application.full_name
                            ?? `Application #${application.id}`}
                    </Link>

                    <p className="mt-1 text-xs text-muted-foreground">
                        {application.email
                            ?? 'Anonymized'}
                    </p>
                </div>
            ),
        },

        {
            key: 'career',
            header: 'Career',

            render: (application) =>
                application.career.title,
        },

        {
            key: 'status',
            header: 'Status',

            render: (application) => (
                <span className="rounded-full border px-2.5 py-1 text-xs font-semibold">
                    {application.status}
                </span>
            ),
        },

        {
            key: 'submitted',
            header: 'Submitted',

            render: (application) =>
                formatDate(
                    application.created_at,
                ),
        },
    ];

    function filter(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        const data = new FormData(
            event.currentTarget,
        );

        router.get(
            '/admin/applications',
            {
                q: String(
                    data.get('q')
                    ?? '',
                ),

                status: String(
                    data.get('status')
                    ?? '',
                ),

                career_id: String(
                    data.get('career_id')
                    ?? '',
                ),
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    return (
        <>
            <Head title="Career Applications" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Applications',
                    },
                ]}
            >
                <PageHeader
                    title="Career Applications"
                    description="Applicant data is private. CV access requires a separate download permission."
                />

                <form
                    onSubmit={filter}
                    className="my-6 grid gap-3 rounded-xl border bg-background p-4 md:grid-cols-[2fr_1fr_1fr_auto]"
                >
                    <input
                        name="q"
                        defaultValue={filters.q}
                        placeholder="Search name or email..."
                        maxLength={100}
                        className="rounded-lg border border-input px-3 py-2"
                    />

                    <select
                        name="status"
                        defaultValue={filters.status}
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All status
                        </option>

                        {statuses.map(
                            (status) => (
                                <option
                                    key={status}
                                    value={status}
                                >
                                    {status}
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        name="career_id"
                        defaultValue={
                            filters.career_id
                            ?? ''
                        }
                        className="rounded-lg border border-input px-3 py-2"
                    >
                        <option value="">
                            All careers
                        </option>

                        {careers.map(
                            (career) => (
                                <option
                                    key={career.id}
                                    value={career.id}
                                >
                                    {career.title}
                                </option>
                            ),
                        )}
                    </select>

                    <button
                        type="submit"
                        className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white hover:bg-cim-navy"
                    >
                        Filter
                    </button>
                </form>

                <DataTable
                    rows={applications.data}
                    columns={columns}
                    rowKey={(application) =>
                        application.id
                    }
                    emptyTitle="No applications"
                    emptyDescription="Belum ada application yang sesuai dengan filter."
                />

                {applications.last_page > 1 && (
                    <nav
                        aria-label="Application pagination"
                        className="mt-6 flex items-center justify-between gap-4"
                    >
                        <div className="text-sm text-muted-foreground">
                            Page {applications.current_page}
                            {' '}of{' '}
                            {applications.last_page}
                            {'  '}
                            {applications.total}
                            {' '}applications
                        </div>

                        <div className="flex gap-2">
                            {applications.prev_page_url ? (
                                <Link
                                    href={
                                        applications
                                            .prev_page_url
                                    }
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

                            {applications.next_page_url ? (
                                <Link
                                    href={
                                        applications
                                            .next_page_url
                                    }
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
