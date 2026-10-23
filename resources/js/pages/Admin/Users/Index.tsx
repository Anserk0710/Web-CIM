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
            header: 'Pengguna',
            render: (user) => (
                <div>
                    <p className='font-medium text-cim-navy'>
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
            header: 'Status Akun',
            render: (user) => (
                <span
                    className={[
                        'inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold',
                        user.is_active
                            ? 'border-green-300 bg-green-50 text-green-800'
                            : 'border-slate-300 bg-slate-100 text-slate-700',
                    ].join(' ')}
                >
                    {user.is_active ? 'Aktif' : 'Nonaktif'}
                </span>
            ),
        },
        {
            key: 'roles',
            header: 'Peran Hak Akses',
            render: (user) => (
                <div className="flex flex-wrap gap-1.5">
                    {user.roles.length > 0 ? (
                        user.roles.map((role) => (
                            <span
                                key={role.id}
                                className="rounded-md border bg-muted/60 px-2.5 py-1 text-xs font-medium"
                            >
                                {role.name}
                            </span>
                        ))
                    ) : (
                        <span className="text-xs text-muted-foreground italic">
                            Belum memiliki peran
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'actions',
            header: 'Tindakan',
            render: (user) => (
                <div className="flex flex-wrap items-center gap-2">
                    {can.disable && (
                        <Button
                            type="button"
                            variant={user.is_active ? 'outline' : 'default'}
                            size="sm"
                            onClick={() => router.patch(
                                `/admin/users/${user.id}/active`,
                                {
                                    is_active: !user.is_active,
                                    lock_version: user.lock_version,
                                },
                            )}
                        >
                            {user.is_active ? 'Nonaktifkan' : 'Aktifkan Akun'}
                        </Button>
                    )}

                    {can.assignRoles && (
                        <div className="flex items-center gap-1.5">
                            <select
                                multiple
                                title="Pilih peran pengguna (tahan Ctrl / Cmd untuk memilih beberapa)"
                                aria-label="Atur peran pengguna"
                                defaultValue={
                                    user.roles.map(
                                        (role) => String(role.id),
                                    )
                                }
                                onBlur={(event) => router.put(
                                    `/admin/users/${user.id}/roles`,
                                    {
                                        role_ids: Array.from(
                                            event.currentTarget.selectedOptions,
                                        ).map((option) => Number(option.value)),
                                        lock_version: user.lock_version,
                                    },
                                )}
                                className="min-h-16 rounded-lg border border-input p-1.5 text-xs focus:ring-1 focus:ring-cim-blue"
                            >
                                {roles.map((role) => (
                                    <option
                                        key={role.id}
                                        value={role.id}
                                    >
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Kelola Pengguna" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pengguna',
                    },
                ]}
            >
                <PageHeader
                    title="Pengguna & Hak Akses"
                    description="Kelola akun pengelola CMS, status keaktifan akun, dan penetapan peran pengguna."
                />

                {can.create && (
                    <form
                        onSubmit={create}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_1fr_auto]"
                    >
                        <input
                            value={createForm.data.name}
                            onChange={(event) => createForm.setData('name', event.target.value)}
                            placeholder="Nama Lengkap"
                            className="rounded-lg border border-input px-3 py-2 text-sm"
                            required
                        />

                        <input
                            type="email"
                            value={createForm.data.email}
                            onChange={(event) => createForm.setData('email', event.target.value)}
                            placeholder="Alamat Email"
                            className="rounded-lg border border-input px-3 py-2 text-sm"
                            required
                        />

                        <Button
                            type="submit"
                            disabled={createForm.processing}
                        >
                            {createForm.processing ? 'Menyimpan...' : 'Tambah Pengguna'}
                        </Button>
                    </form>
                )}

                <DataTable
                    rows={users.data}
                    columns={columns}
                    rowKey={(user) => user.id}
                    emptyTitle="Belum Ada Pengguna"
                    emptyDescription="Silakan tambahkan akun pengguna baru di atas."
                />
            </AdminLayout>
        </>
    );
}