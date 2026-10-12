import {
    Link,
    usePage,
} from '@inertiajs/react';

import {
    type PropsWithChildren,
} from 'react';

import {
    KeyRound,
    LogOut,
} from 'lucide-react';

import {
    type SharedPageProps,
} from '@/types';

export default function AdminLayout({
    children,
}: PropsWithChildren) {
    const {
        app,
        auth,
        navigation,
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

                    <div className="text-right">
                        <p className="text-sm font-medium">
                            {auth.user?.name}
                        </p>

                        <p className="text-xs text-muted-foreground">
                            {auth.user?.email}
                        </p>
                    </div>
                </div>
            </header>

            <div className="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_1fr] lg:px-8">
                <aside className="rounded-xl border bg-background p-3">
                    <nav
                        aria-label="CMS navigation"
                        className="space-y-1"
                    >
                        {navigation.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-muted"
                            >
                                {item.label}
                            </Link>
                        ))}

                        <Link
                            href="/admin/change-password"
                            className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-muted"
                        >
                            <KeyRound
                                className="size-4"
                                aria-hidden="true"
                            />

                            Ganti password
                        </Link>

                        <Link
                            href="/admin/logout"
                            method="post"
                            as="button"
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-destructive hover:bg-muted"
                        >
                            <LogOut
                                className="size-4"
                                aria-hidden="true"
                            />

                            Logout
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