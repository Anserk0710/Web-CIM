import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    Button,
} from '@/components/ui/button';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import {
    ProductForm,
} from '@/components/products/ProductForm';

import {
    humanizeStatus,
} from '@/lib/humanize';

import AdminLayout from '@/layouts/AdminLayout';

import {
    type SharedPageProps,
} from '@/types';

import {
    type ProductFormData,
    type ProductOptions,
} from '@/types/product';

type Product = {
    id: number;
    status: string;
    public_slug: string | null;
    lock_version: number;
    published_at: string | null;
    public_url: string | null;
};

type Props = {
    product: Product;
    form: ProductFormData;
    options: ProductOptions;

    can: {
        publish: boolean;
        archive: boolean;
    };
};

export default function Edit({
    product,
    form,
    options,
    can,
}: Props) {
    const {
        flash,
    } = usePage<SharedPageProps>().props;

    function publish() {
        router.post(
            `/admin/products/${product.id}/publish`,
            {
                lock_version:
                    product.lock_version,
            },
        );
    }

    function unpublish() {
        router.post(
            `/admin/products/${product.id}/unpublish`,
            {
                lock_version:
                    product.lock_version,
            },
        );
    }

    function archive() {
        router.post(
            `/admin/products/${product.id}/archive`,
            {
                lock_version:
                    product.lock_version,
            },
        );
    }

    return (
        <>
            <Head title={`Kelola ${form.name}`} />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Produk',
                        href: '/admin/products',
                    },
                    {
                        label: form.name,
                    },
                ]}
            >
                <PageHeader
                    title={form.name}
                    description={`Status Publikasi: ${humanizeStatus(product.status)}`}
                    actions={(
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href={`/admin/products/${product.id}/preview`}
                                target="_blank"
                                className="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium hover:bg-muted"
                            >
                                Pratinjau Draf
                            </Link>

                            {product.public_url && (
                                <a
                                    href={
                                        product.public_url
                                    }
                                    target="_blank"
                                    rel="noopener"
                                    className="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium hover:bg-muted"
                                >
                                    Lihat di Website
                                </a>
                            )}

                            {can.publish && (
                                <Button
                                    type="button"
                                    onClick={publish}
                                >
                                    Terbitkan
                                </Button>
                            )}

                            {can.publish
                                && product.status
                                === 'PUBLISHED' && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={
                                            unpublish
                                        }
                                    >
                                        Tarik dari Publikasi
                                    </Button>
                                )}

                            {can.archive && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={archive}
                                >
                                    Arsipkan
                                </Button>
                            )}
                        </div>
                    )}
                />

                {flash.success && (
                    <div className="mt-5 rounded-xl bg-cim-ice p-4 text-sm text-cim-navy">
                        {flash.success}
                    </div>
                )}

                <ProductForm
                    key={product.lock_version}
                    initial={{
                        ...form,
                        expected_lock_version:
                            product.lock_version,
                    }}
                    options={options}
                    action={`/admin/products/${product.id}`}
                    method="put"
                    submitLabel="Simpan Perubahan Draf"
                />
            </AdminLayout>
        </>
    );
}