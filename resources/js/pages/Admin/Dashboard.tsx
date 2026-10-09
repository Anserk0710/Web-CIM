import {
    Head,
} from '@inertiajs/react';

import {
    Boxes,
    Database,
    Mail,
} from 'lucide-react';

import AdminLayout from '@/layouts/AdminLayout';

type DashboardProps = {
    phase: string;
};

const cards = [
    {
        title: 'Laravel',
        description: 'Laravel 13 melalui PHP-FPM.',
        icon: Boxes,
    },
    {
        title: 'Database',
        description: 'MySQL tersedia melalui Compose DNS mysql.',
        icon: Database,
    },
    {
        title: 'Mail',
        description: 'Development SMTP diarahkan ke Mailpit.',
        icon: Mail,
    },
];

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
                        CMS Application Foundation
                    </h1>

                    <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground">
                        Halaman ini masih berupa shell. Tujuannya adalah
                        membuktikan Laravel, Inertia, React, TypeScript,
                        Vite, dan Tailwind terintegrasi secara nyata.
                    </p>

                    <div className="mt-8 grid gap-4 md:grid-cols-3">
                        {cards.map((card) => {
                            const Icon = card.icon;

                            return (
                                <article
                                    key={card.title}
                                    className="rounded-2xl border bg-background p-5 shadow-sm"
                                >
                                    <div className="flex size-10 items-center justify-center rounded-xl bg-cim-ice text-cim-navy">
                                        <Icon
                                            aria-hidden="true"
                                            className="size-5"
                                        />
                                    </div>

                                    <h2 className="mt-4 font-semibold text-foreground">
                                        {card.title}
                                    </h2>

                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                        {card.description}
                                    </p>
                                </article>
                            );
                        })}
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}