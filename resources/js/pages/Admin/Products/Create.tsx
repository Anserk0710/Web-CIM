import {
    Head,
} from '@inertiajs/react';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import {
    ProductForm,
} from '@/components/products/ProductForm';

import AdminLayout from '@/layouts/AdminLayout';

import {
    type ProductFormData,
    type ProductOptions,
} from '@/types/product';

type Props = {
    form: ProductFormData;
    options: ProductOptions;
};

export default function Create({
    form,
    options,
}: Props) {
    return (
        <>
            <Head title="Tambah Produk Baru" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Produk',
                        href: '/admin/products',
                    },
                    {
                        label: 'Tambah Baru',
                    },
                ]}
            >
                <PageHeader
                    title="Tambah Produk Baru"
                    description="Buat draf produk baru. Produk ini belum akan ditampilkan ke pengunjung situs sampai Anda menerbitkannya."
                />

                <ProductForm
                    initial={form}
                    options={options}
                    action="/admin/products"
                    method="post"
                    submitLabel="Simpan Sebagai Draf"
                />
            </AdminLayout>
        </>
    );
}