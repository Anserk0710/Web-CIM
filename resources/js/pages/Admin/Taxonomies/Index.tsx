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
        <section className="rounded-xl border bg-background p-5 shadow-sm">
            <h2 className="text-lg font-semibold text-cim-navy">
                {title}
            </h2>

            {canManage && (
                <form
                    onSubmit={submit}
                    className="mt-4 grid gap-3"
                >
                    <input
                        placeholder={`Nama ${title}`}
                        value={form.data.name}
                        onChange={(event) => {
                            const val = event.target.value;
                            form.setData({
                                ...form.data,
                                name: val,
                                slug: val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''),
                            });
                        }}
                        className="rounded-lg border border-input px-3 py-2 text-sm"
                        required
                    />

                    <input
                        placeholder={`Kode URL / Slug`}
                        value={form.data.slug}
                        onChange={(event) => form.setData(
                            'slug',
                            event.target.value,
                        )}
                        className="rounded-lg border border-input px-3 py-2 text-sm font-mono text-xs"
                        required
                    />

                    <Button
                        type="submit"
                        disabled={form.processing}
                        size="sm"
                    >
                        {form.processing ? 'Menyimpan...' : `Tambah ${title}`}
                    </Button>
                </form>
            )}

            <div className="mt-5 divide-y rounded-lg border">
                {items.map((item) => (
                    <div
                        key={item.id}
                        className="flex items-center justify-between gap-4 p-3"
                    >
                        <div>
                            <p className="font-medium text-cim-navy text-sm">
                                {item.name}
                            </p>

                            <p className="text-xs text-muted-foreground font-mono">
                                /{item.slug}
                            </p>
                        </div>

                        <span
                            className={[
                                'inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold',
                                item.is_active
                                    ? 'border-green-300 bg-green-50 text-green-800'
                                    : 'border-slate-300 bg-slate-100 text-slate-700',
                            ].join(' ')}
                        >
                            {item.is_active ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </div>
                ))}

                {items.length === 0 && (
                    <p className="p-4 text-center text-xs text-muted-foreground">
                        Belum ada data {title.toLowerCase()}.
                    </p>
                )}
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
            <Head title="Kategori & Klasifikasi Produk" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Kategori & Klasifikasi',
                    },
                ]}
            >
                <PageHeader
                    title="Kategori & Klasifikasi Produk"
                    description="Kelola kategori utama, merek pabrikan (brand), dan tipe spesifikasi alat medis untuk katalog."
                />

                <div className="mt-8 grid gap-5 lg:grid-cols-3">
                    <TaxonomySection
                        title="Kategori Produk"
                        kind="category"
                        items={categories}
                        canManage={canManage}
                    />

                    <TaxonomySection
                        title="Merek / Brand"
                        kind="brand"
                        items={brands}
                        canManage={canManage}
                    />

                    <TaxonomySection
                        title="Tipe / Klasifikasi"
                        kind="type"
                        items={types}
                        canManage={canManage}
                    />
                </div>
            </AdminLayout>
        </>
    );
}