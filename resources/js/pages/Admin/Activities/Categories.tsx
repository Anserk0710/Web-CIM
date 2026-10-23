import {
    Head,
    router,
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    DataTable,
    type DataTableColumn,
} from '@/components/admin/DataTable';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import {
    Button,
} from '@/components/ui/button';

import AdminLayout from '@/layouts/AdminLayout';

type Category = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    lock_version: number;
};

type Props = {
    categories: Category[];
    canManage: boolean;
};

export default function Categories({
    categories,
    canManage,
}: Props) {
    const form = useForm({
        name: '',
        slug: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post(
            '/admin/activities/categories',
            {
                onSuccess: () =>
                    form.reset(),
            },
        );
    }

    const columns: DataTableColumn<Category>[] = [
        {
            key: 'name',
            header: 'Nama Kategori',
            render: (category) => (
                <span className="font-medium text-cim-navy">
                    {category.name}
                </span>
            ),
        },
        {
            key: 'slug',
            header: 'Tautan Slug',
            render: (category) =>
                category.slug,
        },
        {
            key: 'status',
            header: 'Status',
            render: (category) => (
                <span
                    className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${
                        category.is_active
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                            : 'border-slate-200 bg-slate-50 text-slate-600'
                    }`}
                >
                    {category.is_active ? 'Aktif' : 'Nonaktif'}
                </span>
            ),
        },
        {
            key: 'action',
            header: '',
            render: (category) =>
                canManage ? (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => router.patch(
                            `/admin/activities/categories/${category.id}`,
                            {
                                name:
                                    category.name,

                                slug:
                                    category.slug,

                                is_active:
                                    !category
                                        .is_active,

                                lock_version:
                                    category
                                        .lock_version,
                            },
                        )}
                    >
                        {category.is_active
                            ? 'Nonaktifkan'
                            : 'Aktifkan'}
                    </Button>
                ) : null,
        },
    ];

    return (
        <>
            <Head title="Kategori Berita & Kegiatan" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Berita & Kegiatan',
                        href: '/admin/activities',
                    },
                    {
                        label: 'Kategori',
                    },
                ]}
            >
                <PageHeader
                    title="Kategori Berita & Kegiatan"
                    description="Kelola pengelompokan topik untuk artikel berita dan kegiatan perusahaan."
                />

                {canManage && (
                    <form
                        onSubmit={submit}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_1fr_auto]"
                    >
                        <input
                            placeholder="Nama kategori baru..."
                            value={
                                form.data.name
                            }
                            onChange={(event) => form.setData(
                                'name',
                                event.target.value,
                            )}
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <input
                            placeholder="slug-kategori (otomatis atau kustom)"
                            value={
                                form.data.slug
                            }
                            onChange={(event) => form.setData(
                                'slug',
                                event.target.value,
                            )}
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <Button type="submit">
                            Tambah Kategori
                        </Button>
                    </form>
                )}

                <DataTable
                    rows={categories}
                    columns={columns}
                    rowKey={(category) =>
                        category.id
                    }
                    emptyTitle="Belum Ada Kategori"
                    emptyDescription="Tambahkan kategori pertama untuk mengelompokkan berita dan kegiatan."
                />
            </AdminLayout>
        </>
    );
}