import { Head, router, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

type Permission = {
    id: number;
    name: string;
};

type Role = {
    id: number;
    name: string;
    description: string | null;
    is_system: boolean;
    lock_version: number;
    permissions: Permission[];
};

type Props = {
    roles: Role[];
    permissions: Permission[];
    canManage: boolean;
};

export default function Index({
    roles,
    permissions,
    canManage,
}: Props) {
    const createForm = useForm({
        name: '',
        description: '',
    });

    function create(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        createForm.post(
            '/admin/roles',
            {
                onSuccess: () =>
                    createForm.reset(),
            },
        );
    }

    return (
        <>
            <Head title="Roles" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Roles',
                    },
                ]}
            >
                <PageHeader
                    title="Roles & Permissions"
                    description="Super Admin system semantics remain immutable."
                />

                {canManage && (
                    <form
                        onSubmit={create}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_2fr_auto]"
                    >
                        <input
                            placeholder="Role name"
                            value={
                                createForm
                                    .data.name
                            }
                            onChange={(event) => createForm.setData(
                                'name',
                                event.target.value,
                            )}
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <input
                            placeholder="Description"
                            value={
                                createForm
                                    .data
                                    .description
                            }
                            onChange={(event) => createForm.setData(
                                'description',
                                event.target.value,
                            )}
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <Button type="submit">
                            Create
                        </Button>
                    </form>
                )}

                <div className="space-y-5">
                    {roles.map((role) => (
                        <section
                            key={role.id}
                            className="rounded-xl border bg-background p-5"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h2 className="font-semibold text-cim-navy">
                                        {role.name}
                                    </h2>

                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {
                                            role.description
                                        }
                                    </p>
                                </div>

                                {role.is_system && (
                                    <span className="rounded-full bg-cim-ice px-3 py-1 text-xs font-semibold">
                                        SYSTEM
                                    </span>
                                )}
                            </div>

                            <div className="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                {permissions.map(
                                    (permission) => {
                                        const checked =
                                            role.permissions
                                                .some(
                                                    (
                                                        item,
                                                    ) =>
                                                        item.id
                                                        === permission.id,
                                                );

                                        return (
                                            <label
                                                key={
                                                    permission.id
                                                }
                                                className="flex items-center gap-2 rounded-lg border p-3 text-xs"
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={
                                                        checked
                                                    }
                                                    disabled={
                                                        !canManage
                                                        || role
                                                            .is_system
                                                    }
                                                    onChange={() => {
                                                        const current =
                                                            role.permissions
                                                                .map(
                                                                    (
                                                                        item,
                                                                    ) =>
                                                                        item.id,
                                                                );

                                                        const next =
                                                            checked
                                                                ? current.filter(
                                                                    (
                                                                        id,
                                                                    ) =>
                                                                        id
                                                                        !== permission.id,
                                                                )
                                                                : [
                                                                    ...current,
                                                                    permission.id,
                                                                ];

                                                        router.put(
                                                            `/admin/roles/${role.id}/permissions`,
                                                            {
                                                                permission_ids:
                                                                    next,

                                                                lock_version:
                                                                    role
                                                                        .lock_version,
                                                            },
                                                        );
                                                    }}
                                                />

                                                {
                                                    permission.name
                                                }
                                            </label>
                                        );
                                    },
                                )}
                            </div>
                        </section>
                    ))}
                </div>
            </AdminLayout>
        </>
    );
}