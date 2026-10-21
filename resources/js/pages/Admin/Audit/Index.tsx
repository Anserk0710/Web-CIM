import { Head, Link, router } from '@inertiajs/react';
import { DataTable, type DataTableColumn } from '@/components/admin/DataTable';
import { PageHeader } from '@/components/admin/PageHeader';
import AdminLayout from '@/layouts/AdminLayout';

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
            header: 'Action',
            render: (audit) => (
                <Link
                    href={`/admin/audit/${audit.id}`}
                    className="font-semibold text-cim-blue"
                >
                    {audit.action}
                </Link>
            ),
        },
        {
            key: 'actor',
            header: 'Actor',
            render: (audit) =>
                audit.actor
                    ? audit.actor.name
                    : 'System / Guest',
        },
        {
            key: 'entity',
            header: 'Entity',
            render: (audit) =>
                audit.entity_type
                    ? `${audit.entity_type} #${audit.entity_id ?? '—'}`
                    : '—',
        },
        {
            key: 'result',
            header: 'Result',
            render: (audit) =>
                audit.result,
        },
        {
            key: 'created',
            header: 'Created',
            render: (audit) =>
                audit.created_at,
        },
    ];

    return (
        <>
            <Head title="Audit Log" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Audit',
                    },
                ]}
            >
                <PageHeader
                    title="Audit Log"
                    description="Append-only sensitive action history."
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
                        placeholder="Action"
                        className="rounded-lg border border-input px-3 py-2"
                    />

                    <input
                        name="entity_type"
                        defaultValue={
                            filters.entity_type
                        }
                        placeholder="Entity type"
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
                            All result
                        </option>

                        <option value="SUCCESS">
                            SUCCESS
                        </option>

                        <option value="DENIED">
                            DENIED
                        </option>

                        <option value="FAILURE">
                            FAILURE
                        </option>
                    </select>

                    <button className="rounded-lg bg-cim-blue px-5 py-2 text-sm font-semibold text-white">
                        Filter
                    </button>
                </form>

                <DataTable
                    rows={logs.data}
                    columns={columns}
                    rowKey={(audit) =>
                        audit.id
                    }
                    emptyTitle="Audit log kosong"
                    emptyDescription="Sensitive actions akan tampil di sini."
                />
            </AdminLayout>
        </>
    );
}