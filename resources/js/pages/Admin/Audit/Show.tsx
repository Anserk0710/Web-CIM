import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/admin/PageHeader';
import AdminLayout from '@/layouts/AdminLayout';
import {
    formatIndonesianDate,
    humanizeAction,
    humanizeEntity,
    humanizeStatus,
} from '@/lib/humanize';

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
            <Head title={`Detail Audit #${audit.id}`} />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Catatan Aktivitas',
                        href: '/admin/audit',
                    },
                    {
                        label: `#${audit.id}`,
                    },
                ]}
            >
                <PageHeader
                    title={humanizeAction(audit.action)}
                    description={`Status Eksekusi: ${humanizeStatus(audit.result)}`}
                />

                <dl className="mt-8 grid gap-4 rounded-xl border bg-background p-6 md:grid-cols-2">
                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Pelaku Tindakan
                        </dt>

                        <dd className="mt-1 font-medium">
                            {audit.actor
                                ? `${audit.actor.name} (${audit.actor.email})`
                                : 'Sistem Otomatis / Pengunjung Situs'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Data yang Terkena Dampak
                        </dt>

                        <dd className="mt-1 font-medium">
                            {audit.entity_type
                                ? `${humanizeEntity(audit.entity_type)} #${audit.entity_id ?? ''}`
                                : '—'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Waktu Kejadian
                        </dt>

                        <dd className="mt-1 font-medium">
                            {formatIndonesianDate(audit.created_at)}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Alamat IP Akses
                        </dt>

                        <dd className="mt-1">
                            {audit.ip ?? '—'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            ID Jejak Permintaan (Request ID)
                        </dt>

                        <dd className="mt-1 break-all text-xs text-muted-foreground">
                            {audit.request_id ?? '—'}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-xs text-muted-foreground">
                            Informasi Peramban (User Agent)
                        </dt>

                        <dd className="mt-1 break-all text-xs text-muted-foreground">
                            {audit.user_agent ?? '—'}
                        </dd>
                    </div>
                </dl>

                <section className="mt-6 rounded-xl border bg-background p-6">
                    <h2 className="font-semibold text-cim-navy">
                        Detail Perubahan Nilai Data
                    </h2>

                    {audit.changes && Object.keys(audit.changes).length > 0 ? (
                        <pre className="mt-4 overflow-x-auto rounded-lg bg-muted p-4 text-xs font-mono">
                            {JSON.stringify(
                                audit.changes,
                                null,
                                2,
                            )}
                        </pre>
                    ) : (
                        <p className="mt-3 text-sm text-muted-foreground">
                            Tidak ada rekaman data sebelum dan sesudah perubahan untuk tindakan ini.
                        </p>
                    )}
                </section>
            </AdminLayout>
        </>
    );
}