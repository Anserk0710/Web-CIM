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
    Breadcrumbs,
    type BreadcrumbItem,
} from '@/components/admin/Breadcrumbs';

import {
    NavigationLoadingBar,
} from '@/components/admin/NavigationLoadingBar';

import {
    Toast,
} from '@/components/admin/Toast';

import {
    type SharedPageProps,
} from '@/types';

type Props = PropsWithChildren<{
    breadcrumbs?: BreadcrumbItem[];
}>;

export default function AdminLayout({
    children,
    breadcrumbs = [],
}: Props) {
    const {
        app,
        auth,
        navigation,
        flash,
    } = usePage<SharedPageProps>().props;

    return (
        <div className="min-h-screen bg-muted/50">
            <NavigationLoadingBar />

            <Toast
                message={
                    flash.success
                    ?? flash.status
                }
            />

            <header className="border-b bg-background">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <div>
                        <p className="text-sm font-semibold text-cim-blue">
                            CIM CMS
                        </p>

                        <p className="text-xs text-muted-foreground">
                            {app.name}
                        </p>
                    </div>

                    <div className="flex items-center gap-4">
                        <span className="rounded-full bg-cim-ice px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cim-navy">
                            {app.environment}
                        </span>

                        <div className="text-right">
                            <p className="text-sm font-medium">
                                {auth.user?.name}
                            </p>

                            <p className="text-xs text-muted-foreground">
                                {auth.user?.email}
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <div className="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:px-8">
                <aside className="overflow-x-auto rounded-xl border bg-background p-3 lg:overflow-visible">
                    <nav
                        aria-label="CMS navigation"
                        className="flex min-w-max gap-1 lg:block lg:min-w-0 lg:space-y-1"
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

                            Password
                        </Link>

                        <Link
                            href="/admin/logout"
                            method="post"
                            as="button"
                            className="flex items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-destructive hover:bg-muted lg:w-full"
                        >
                            <LogOut
                                className="size-4"
                                aria-hidden="true"
                            />

                            Logout
                        </Link>
                    </nav>
                </aside>

                <main className="min-w-0">
                    {breadcrumbs.length > 0 && (
                        <Breadcrumbs
                            items={breadcrumbs}
                        />
                    )}

                    {children}
                </main>
            </div>
        </div>
    );
}