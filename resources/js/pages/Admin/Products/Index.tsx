import {
    Head,
    Link,
} from '@inertiajs/react';

import {
    EmptyState,
} from '@/components/admin/EmptyState';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import {
    humanizeStatus,
} from '@/lib/humanize';

import AdminLayout from '@/layouts/AdminLayout';

type Product = {
    id: number;
    status: string;
    public_slug: string | null;
    lock_version: number;
    name: string | null;
    requested_slug: string | null;
    published_at: string | null;
};

type Props = {
    products: {
        data: Product[];
    };

    can: {
        create: boolean;
    };
};

export default function Index({
    products,
    can,
}: Props) {
    return (
        <>
            <Head title="Katalog Produk" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Produk',
                    },
                ]}
            >
                <PageHeader
                    title="Katalog Produk Medis"
                    description="Kelola informasi produk, gambar, brosur, dan status penerbitan produk ke situs web."
                    actions={
                        can.create ? (
                            <Link
                                href="/admin/products/create"
                                className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white hover:bg-cim-navy"
                            >
                                Tambah Produk Baru
                            </Link>
                        ) : undefined
                    }
                />

                {products.data.length === 0 ? (
                    <div className="mt-8">
                        <EmptyState
                            title="Belum Ada Produk"
                            description="Silakan tambahkan produk pertama Anda untuk mulai menampilkannya di katalog."
                        />
                    </div>
                ) : (
                    <div className="mt-8 overflow-x-auto rounded-xl border bg-background">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-3">
                                        Nama Produk
                                    </th>

                                    <th className="px-4 py-3">
                                        Status Publikasi
                                    </th>

                                    <th className="px-4 py-3">
                                        Tautan / Slug Halaman
                                    </th>

                                    <th className="px-4 py-3">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {products.data.map(
                                    (product) => (
                                        <tr
                                            key={
                                                product.id
                                            }
                                            className="border-b last:border-0"
                                        >
                                            <td className="px-4 py-3 font-medium">
                                                {product.name
                                                    ?? `Produk #${product.id}`}
                                            </td>

                                            <td className="px-4 py-3">
                                                <span className="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold">
                                                    {humanizeStatus(product.status)}
                                                </span>
                                            </td>

                                            <td className="px-4 py-3 text-muted-foreground">
                                                {product.public_slug
                                                    ?? '—'}
                                            </td>

                                            <td className="px-4 py-3">
                                                <Link
                                                    href={`/admin/products/${product.id}/edit`}
                                                    className="font-medium text-cim-blue hover:text-cim-navy"
                                                >
                                                    Kelola & Edit
                                                </Link>
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>
                )}
            </AdminLayout>
        </>
    );
}