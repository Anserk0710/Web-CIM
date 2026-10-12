import {
    Head,
} from '@inertiajs/react';

import AdminLayout from '@/layouts/AdminLayout';

type RoleRow = {
    id: number;
    name: string;
    description: string | null;
    is_system: boolean;
    lock_version: number;
    permissions: string[];
};

type Props = {
    roles: RoleRow[];
};

export default function Index({
    roles,
}: Props) {
    return (
        <>
            <Head title="Roles" />

            <AdminLayout>
                <section>
                    <h1 className="text-3xl font-bold text-cim-navy">
                        Roles & Permissions
                    </h1>

                    <div className="mt-6 space-y-4">
                        {roles.map((role) => (
                            <article
                                key={role.id}
                                className="rounded-xl border bg-background p-5"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 className="font-semibold">
                                            {role.name}
                                        </h2>

                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {role.description}
                                        </p>
                                    </div>

                                    {role.is_system && (
                                        <span className="rounded-full bg-cim-ice px-3 py-1 text-xs font-semibold text-cim-navy">
                                            System
                                        </span>
                                    )}
                                </div>

                                <div className="mt-4 flex flex-wrap gap-2">
                                    {role.permissions.map(
                                        (permission) => (
                                            <span
                                                key={
                                                    permission
                                                }
                                                className="rounded-md bg-muted px-2 py-1 text-xs"
                                            >
                                                {permission}
                                            </span>
                                        ),
                                    )}
                                </div>
                            </article>
                        ))}
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}