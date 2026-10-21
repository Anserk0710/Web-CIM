import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/admin/PageHeader';
import AdminLayout from '@/layouts/AdminLayout';

type Props = {
    audit: {
        id: number;
        action: string;
        entity_type: string | null;
        entity_id: number | null;
        result: string;
        changes: Record<string, unknown> | null;
        request_id: string | null;
        ip: string | null;
        user_agent: string | null;
        created_at: string;

        actor: {
            name: string;
            email: string;
        } | null;
    };
};

export default function Show({
    audit,
}: Props) {
    return (
        <>
            <Head title={`Audit #${audit.id}`} />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Audit',
                        href: '/admin/audit',
                    },
                    {
                        label: `#${audit.id}`,
                    },
                ]}
            >
                <PageHeader
                    title={audit.action}
                    description={`Result: ${audit.result}`}
                />

                <dl className="mt-8 grid gap-4 rounded-xl border bg-background p-6 md:grid-cols-2">
                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Actor
                        </dt>

                        <dd className="mt-1">
                            {audit.actor
                                ? `${audit.actor.name} (${audit.actor.email})`
                                : 'System / Guest'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Entity
                        </dt>

                        <dd className="mt-1">
                            {audit.entity_type
                                ?? '—'}
                            {' '}
                            {audit.entity_id
                                ?? ''}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Request ID
                        </dt>

                        <dd className="mt-1 break-all">
                            {audit.request_id
                                ?? '—'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            IP
                        </dt>

                        <dd className="mt-1">
                            {audit.ip ?? '—'}
                        </dd>
                    </div>
                </dl>

                <section className="mt-6 rounded-xl border bg-background p-6">
                    <h2 className="font-semibold text-cim-navy">
                        Changes
                    </h2>

                    <pre className="mt-4 overflow-x-auto rounded-lg bg-muted p-4 text-xs">
                        {JSON.stringify(
                            audit.changes,
                            null,
                            2,
                        )}
                    </pre>
                </section>
            </AdminLayout>
        </>
    );
}