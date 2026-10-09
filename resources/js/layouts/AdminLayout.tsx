import {
    Link,
    usePage,
} from '@inertiajs/react';

import {
    type PropsWithChildren,
} from 'react';

import {
    LayoutDashboard,
    LogIn,
} from 'lucide-react';

type SharedPageProps = {
    app: {
        name: string;
        environment: string;
    };
};

export default function AdminLayout({
    children,
}: PropsWithChildren) {
    const {
        app,
    } = usePage<SharedPageProps>().props;

    return (
        <div className="min-h-screen bg-muted/50">
            <header className="border-b bg-background">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div>
                        <p className="text-sm font-semibold text-cim-blue">
                            CIM CMS
                        </p>

                        <p className="text-xs text-muted-foreground">
                            {app.name}
                        </p>
                    </div>

                    <span className="rounded-full bg-cim-ice px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cim-navy">
                        {app.environment}
                    </span>
                </div>
            </header>

            <div className="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[220px_1fr] lg:px-8">
                <aside className="rounded-xl border bg-background p-3">
                    <nav
                        aria-label="CMS navigation"
                        className="space-y-1"
                    >
                        <Link
                            href="/admin"
                            className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted"
                        >
                            <LayoutDashboard
                                aria-hidden="true"
                                className="size-4"
                            />

                            Dashboard
                        </Link>

                        <Link
                            href="/admin/login"
                            className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted"
                        >
                            <LogIn
                                aria-hidden="true"
                                className="size-4"
                            />

                            Login shell
                        </Link>
                    </nav>
                </aside>

                <main>
                    {children}
                </main>
            </div>
        </div>
    );
}