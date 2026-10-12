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
            <Head title="Products" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Products',
                    },
                ]}
            >
                <PageHeader
                    title="Products"
                    description="WORKING revision terpisah dari konten PUBLISHED yang sedang live."
                    actions={
                        can.create ? (
                            <Link
                                href="/admin/products/create"
                                className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white"
                            >
                                Create Product
                            </Link>
                        ) : undefined
                    }
                />

                {products.data.length === 0 ? (
                    <div className="mt-8">
                        <EmptyState
                            title="Belum ada product"
                            description="Buat product pertama untuk memulai vertical slice."
                        />
                    </div>
                ) : (
                    <div className="mt-8 overflow-x-auto rounded-xl border bg-background">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-3">
                                        Product
                                    </th>

                                    <th className="px-4 py-3">
                                        Status
                                    </th>

                                    <th className="px-4 py-3">
                                        Public slug
                                    </th>

                                    <th className="px-4 py-3">
                                        Action
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
                                                    ?? `#${product.id}`}
                                            </td>

                                            <td className="px-4 py-3">
                                                {
                                                    product.status
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {product.public_slug
                                                    ?? '—'}
                                            </td>

                                            <td className="px-4 py-3">
                                                <Link
                                                    href={`/admin/products/${product.id}/edit`}
                                                    className="font-medium text-cim-blue"
                                                >
                                                    Edit
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