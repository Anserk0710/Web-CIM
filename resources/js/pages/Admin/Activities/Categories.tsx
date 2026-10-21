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
            header: 'Name',
            render: (category) =>
                category.name,
        },
        {
            key: 'slug',
            header: 'Slug',
            render: (category) =>
                category.slug,
        },
        {
            key: 'status',
            header: 'Status',
            render: (category) =>
                category.is_active
                    ? 'Active'
                    : 'Inactive',
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
                            ? 'Disable'
                            : 'Enable'}
                    </Button>
                ) : null,
        },
    ];

    return (
        <>
            <Head title="Activity Categories" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Activities',
                        href: '/admin/activities',
                    },
                    {
                        label: 'Categories',
                    },
                ]}
            >
                <PageHeader
                    title="Activity Categories"
                />

                {canManage && (
                    <form
                        onSubmit={submit}
                        className="my-6 grid gap-3 rounded-xl border bg-background p-4 sm:grid-cols-[1fr_1fr_auto]"
                    >
                        <input
                            placeholder="Name"
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
                            placeholder="slug"
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
                            Add
                        </Button>
                    </form>
                )}

                <DataTable
                    rows={categories}
                    columns={columns}
                    rowKey={(category) =>
                        category.id
                    }
                    emptyTitle="No categories"
                    emptyDescription="Create Activity category."
                />
            </AdminLayout>
        </>
    );
}