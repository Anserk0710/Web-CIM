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
            <Head title="Create Product" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Products',
                        href: '/admin/products',
                    },
                    {
                        label: 'Create',
                    },
                ]}
            >
                <PageHeader
                    title="Create Product"
                    description="Membuat WORKING revision. Product belum terlihat di website publik sampai dipublish."
                />

                <ProductForm
                    initial={form}
                    options={options}
                    action="/admin/products"
                    method="post"
                    submitLabel="Save draft"
                />
            </AdminLayout>
        </>
    );
}