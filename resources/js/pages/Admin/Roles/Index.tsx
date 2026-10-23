import { Head, router, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';
import { humanizePermission } from '@/lib/humanize';

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
            <Head title="Peran & Hak Akses" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Peran Hak Akses',
                    },
                ]}
            >
                <PageHeader
                    title="Peran & Wewenang Hak Akses"
                    description="Kelola peran staf dan wewenang operasional sistem. Peran bawaan sistem dikunci demi integritas keamanan data."
                />

                {canManage && (
                    <form
                        onSubmit={create}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_2fr_auto]"
                    >
                        <input
                            placeholder="Nama peran baru (contoh: Editor Konten)"
                            value={createForm.data.name}
                            onChange={(event) => createForm.setData('name', event.target.value)}
                            className="rounded-lg border border-input px-3 py-2 text-sm"
                            required
                        />

                        <input
                            placeholder="Deskripsi atau tanggung jawab peran ini"
                            value={createForm.data.description}
                            onChange={(event) => createForm.setData('description', event.target.value)}
                            className="rounded-lg border border-input px-3 py-2 text-sm"
                        />

                        <Button
                            type="submit"
                            disabled={createForm.processing}
                        >
                            {createForm.processing ? 'Menyimpan...' : 'Tambah Peran'}
                        </Button>
                    </form>
                )}

                <div className="space-y-5">
                    {roles.map((role) => (
                        <section
                            key={role.id}
                            className="rounded-xl border bg-background p-5 shadow-sm"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h2 className="text-lg font-semibold text-cim-navy">
                                        {role.name}
                                    </h2>

                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {role.description || 'Tidak ada keterangan tambahan.'}
                                    </p>
                                </div>

                                {role.is_system && (
                                    <span className="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-800">
                                        Bawaan Sistem
                                    </span>
                                )}
                            </div>

                            <div className="mt-5 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
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
                                                key={permission.id}
                                                className={[
                                                    'flex items-start gap-2.5 rounded-lg border p-3 text-xs transition-colors',
                                                    checked ? 'border-cim-blue/40 bg-cim-ice/20' : 'border-border/60 hover:bg-muted/30',
                                                    !canManage || role.is_system ? 'cursor-not-allowed opacity-80' : 'cursor-pointer',
                                                ].join(' ')}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={checked}
                                                    disabled={!canManage || role.is_system}
                                                    className="mt-0.5 rounded border-input text-cim-blue focus:ring-cim-blue"
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
                                                                permission_ids: next,
                                                                lock_version: role.lock_version,
                                                            },
                                                        );
                                                    }}
                                                />

                                                <div>
                                                    <span className="font-medium text-foreground">
                                                        {humanizePermission(permission.name)}
                                                    </span>
                                                    <span className="mt-0.5 block font-mono text-[10px] text-muted-foreground">
                                                        {permission.name}
                                                    </span>
                                                </div>
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