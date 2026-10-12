import {
    Head,
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import AdminLayout from '@/layouts/AdminLayout';

type Taxonomy = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    lock_version: number;
};

type Props = {
    categories: Taxonomy[];
    brands: Taxonomy[];
    types: Taxonomy[];
    canManage: boolean;
};

type SectionProps = {
    title: string;
    kind: 'category' | 'brand' | 'type';
    items: Taxonomy[];
    canManage: boolean;
};

function TaxonomySection({
    title,
    kind,
    items,
    canManage,
}: SectionProps) {
    const form = useForm({
        name: '',
        slug: '',
        description: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post(
            `/admin/taxonomies/${kind}`,
            {
                onSuccess: () => form.reset(),
            },
        );
    }

    return (
        <section className="rounded-xl border bg-background p-5">
            <h2 className="text-lg font-semibold text-cim-navy">
                {title}
            </h2>

            {canManage && (
                <form
                    onSubmit={submit}
                    className="mt-4 grid gap-3"
                >
                    <input
                        placeholder="Name"
                        value={form.data.name}
                        onChange={(event) => form.setData(
                            'name',
                            event.target.value,
                        )}
                        className="rounded-lg border border-input px-3 py-2"
                        required
                    />

                    <input
                        placeholder="slug"
                        value={form.data.slug}
                        onChange={(event) => form.setData(
                            'slug',
                            event.target.value,
                        )}
                        className="rounded-lg border border-input px-3 py-2"
                        required
                    />

                    <Button
                        type="submit"
                        disabled={form.processing}
                    >
                        Add {title}
                    </Button>
                </form>
            )}

            <div className="mt-5 divide-y rounded-lg border">
                {items.map((item) => (
                    <div
                        key={item.id}
                        className="flex justify-between gap-4 p-3"
                    >
                        <div>
                            <p className="font-medium">
                                {item.name}
                            </p>

                            <p className="text-xs text-muted-foreground">
                                {item.slug}
                            </p>
                        </div>

                        <span className="text-xs">
                            {item.is_active
                                ? 'Active'
                                : 'Inactive'}
                        </span>
                    </div>
                ))}
            </div>
        </section>
    );
}

export default function Index({
    categories,
    brands,
    types,
    canManage,
}: Props) {
    return (
        <>
            <Head title="Taxonomies" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Taxonomies',
                    },
                ]}
            >
                <PageHeader
                    title="Product Taxonomies"
                    description="Category adalah primary taxonomy; Brand dan Type merupakan controlled masters."
                />

                <div className="mt-8 grid gap-5 lg:grid-cols-3">
                    <TaxonomySection
                        title="Category"
                        kind="category"
                        items={categories}
                        canManage={canManage}
                    />

                    <TaxonomySection
                        title="Brand"
                        kind="brand"
                        items={brands}
                        canManage={canManage}
                    />

                    <TaxonomySection
                        title="Type"
                        kind="type"
                        items={types}
                        canManage={canManage}
                    />
                </div>
            </AdminLayout>
        </>
    );
}