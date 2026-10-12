import {
    Head,
} from '@inertiajs/react';

import {
    KeyRound,
    ShieldCheck,
    UserCheck,
} from 'lucide-react';

import AdminLayout from '@/layouts/AdminLayout';

type DashboardProps = {
    phase: string;
};

export default function Dashboard({
    phase,
}: DashboardProps) {
    return (
        <>
            <Head title="CMS Dashboard" />

            <AdminLayout>
                <section>
                    <p className="text-sm font-semibold text-cim-blue">
                        {phase}
                    </p>

                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-cim-navy">
                        Security Foundation
                    </h1>

                    <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground">
                        Dashboard hanya menampilkan surface
                        yang diizinkan. Counts domain pribadi
                        tidak dikirim pada Phase 2.
                    </p>

                    <div className="mt-8 grid gap-4 md:grid-cols-3">
                        <article className="rounded-xl border bg-background p-5">
                            <UserCheck className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold">
                                Authentication
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Login, logout, reset dan change
                                password aktif.
                            </p>
                        </article>

                        <article className="rounded-xl border bg-background p-5">
                            <ShieldCheck className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold">
                                RBAC
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Authorization server-side
                                deny-by-default.
                            </p>
                        </article>

                        <article className="rounded-xl border bg-background p-5">
                            <KeyRound className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold">
                                Audit
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Mutation sensitif memiliki
                                audit trail append-only.
                            </p>
                        </article>
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}