import { Head, router, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { DataTable, type DataTableColumn } from '@/components/admin/DataTable';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

type Role = {
    id: number;
    name: string;
};

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    lock_version: number;
    roles: Role[];
};

type Props = {
    users: {
        data: UserRow[];
    };

    roles: Role[];

    can: {
        create: boolean;
        update: boolean;
        disable: boolean;
        assignRoles: boolean;
    };
};

export default function Index({
    users,
    roles,
    can,
}: Props) {
    const createForm = useForm({
        name: '',
        email: '',
    });

    function create(
        event: FormEvent<HTMLFormElement>,
    ) {
        event?.preventDefault();

        createForm.post(
            '/admin/users',
            { onSuccess: () => createForm.reset() }
        );
    }

    const columns: DataTableColumn<UserRow>[] = [
        {
            key: 'user',
            header: 'User',
            render: (user) => (
                <div>
                    <p className='font-medium'>
                        {user.name}
                    </p>
                    <p className='text-xs text-muted-foreground'>
                        {user.email}
                    </p>
                </div>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            render: (user) => user.is_active
                ? 'Active'
                : 'Inactive',
        },
        {
            key: 'roles',
            header: 'Roles',
            render: (user) => (
                <div className="flex flex-wrap gap-2">
                    {user.roles.map(
                        (role) => (
                            <span
                                key={role.id}
                                className="rounded-full bg-muted px-2 py-1 text-xs"
                            >
                                {role.name}
                            </span>
                        ),
                    )}
                </div>
            ),
        },
        {
            key: 'actions',
            header: 'Action',
            render: (user) => (
                <div className="flex flex-wrap gap-2">
                    {can.disable && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => router.patch(
                                `/admin/users/${user.id}/active`,
                                {
                                    is_active:
                                        !user
                                            .is_active,

                                    lock_version:
                                        user
                                            .lock_version,
                                },
                            )}
                        >
                            {user.is_active
                                ? 'Deactivate'
                                : 'Activate'}
                        </Button>
                    )}

                    {can.assignRoles && (
                        <select
                            multiple
                            defaultValue={
                                user.roles.map(
                                    (role) =>
                                        String(
                                            role.id,
                                        ),
                                )
                            }
                            onBlur={(event) => router.put(
                                `/admin/users/${user.id}/roles`,
                                {
                                    role_ids:
                                        Array.from(
                                            event
                                                .currentTarget
                                                .selectedOptions,
                                        ).map(
                                            (
                                                option,
                                            ) =>
                                                Number(
                                                    option
                                                        .value,
                                                ),
                                        ),

                                    lock_version:
                                        user
                                            .lock_version,
                                },
                            )}
                            className="min-h-20 rounded-lg border border-input p-2 text-xs"
                        >
                            {roles.map(
                                (role) => (
                                    <option
                                        key={
                                            role.id
                                        }
                                        value={
                                            role.id
                                        }
                                    >
                                        {role.name}
                                    </option>
                                ),
                            )}
                        </select>
                    )}
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Users" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Users',
                    },
                ]}
            >
                <PageHeader
                    title="Users"
                    description="Account activation and role assignment are server-authorized."
                />

                {can.create && (
                    <form
                        onSubmit={create}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_1fr_auto]"
                    >
                        <input
                            value={
                                createForm
                                    .data.name
                            }
                            onChange={(event) => createForm.setData(
                                'name',
                                event.target.value,
                            )}
                            placeholder="Name"
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <input
                            type="email"
                            value={
                                createForm
                                    .data.email
                            }
                            onChange={(event) => createForm.setData(
                                'email',
                                event.target.value,
                            )}
                            placeholder="Email"
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <Button type="submit">
                            Create
                        </Button>
                    </form>
                )}

                <DataTable
                    rows={users.data}
                    columns={columns}
                    rowKey={(user) =>
                        user.id
                    }
                    emptyTitle="No users"
                    emptyDescription="Create first CMS user."
                />
            </AdminLayout>
        </>
    );
}