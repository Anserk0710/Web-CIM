import {
    Head,
    router,
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    FormField,
} from '@/components/admin/FormField';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import {
    Button,
} from '@/components/ui/button';

import AdminLayout from '@/layouts/AdminLayout';

type ProductOption = {
    id: number;
    name: string;
};

type FormData = {
    expected_lock_version: number | null;
    product_id: number | null;
    catalog_code: string;
    procurement_title: string;
    procurement_category: string;
    notes: string;
    unit_package: string;
    availability: string;
    reference_url: string;
    effective_at: string | null;
    expires_at: string | null;
    sort_order: number;
    seo_title: string;
    seo_description: string;
};

type Props = {
    entry: {
        id: number;
        status: string;
        lock_version: number;
    } | null;

    form: FormData;
    products: ProductOption[];
    availabilities: string[];

    can?: {
        publish: boolean;
        archive: boolean;
        delete: boolean;
    };
};

export default function Form({
    entry,
    form: initial,
    products,
    availabilities,
    can,
}: Props) {
    const form = useForm<FormData>(
        initial,
    );

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        if (entry === null) {
            form.post('/admin/ecatalog');

            return;
        }

        form.put(
            `/admin/ecatalog/${entry.id}`,
        );
    }

    return (
        <>
            <Head
                title={
                    entry
                        ? 'Edit E-Catalog'
                        : 'Create E-Catalog'
                }
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'E-Catalog',
                        href: '/admin/ecatalog',
                    },
                    {
                        label: entry
                            ? 'Edit'
                            : 'Create',
                    },
                ]}
            >
                <PageHeader
                    title={
                        entry
                            ? 'Edit E-Catalog'
                            : 'Create E-Catalog'
                    }
                    description={
                        entry
                            ? `Status: ${entry.status}`
                            : 'New record starts as DRAFT.'
                    }
                    actions={
                        entry && can ? (
                            <div className="flex flex-wrap gap-2">
                                {can.publish && (
                                    <Button
                                        type="button"
                                        onClick={() => router.post(
                                            `/admin/ecatalog/${entry.id}/publish`,
                                            {
                                                lock_version:
                                                    entry
                                                        .lock_version,
                                            },
                                        )}
                                    >
                                        Publish
                                    </Button>
                                )}

                                {can.archive && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => router.post(
                                            `/admin/ecatalog/${entry.id}/archive`,
                                            {
                                                lock_version:
                                                    entry
                                                        .lock_version,
                                            },
                                        )}
                                    >
                                        Archive
                                    </Button>
                                )}

                                {can.delete
                                    && entry.status
                                    === 'DRAFT' && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            onClick={() => {
                                                if (
                                                    window.confirm(
                                                        'Delete draft E-Catalog?',
                                                    )
                                                ) {
                                                    router.delete(
                                                        `/admin/ecatalog/${entry.id}`,
                                                    );
                                                }
                                            }}
                                        >
                                            Delete
                                        </Button>
                                    )}
                            </div>
                        ) : undefined
                    }
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6 rounded-2xl border bg-background p-6"
                >
                    <div className="grid gap-5 md:grid-cols-2">
                        <FormField
                            label="Product"
                            htmlFor="product_id"
                            error={form.errors.product_id}
                            required
                        >
                            <select
                                id="product_id"
                                value={
                                    form.data.product_id
                                    ?? ''
                                }
                                onChange={(event) => form.setData(
                                    'product_id',
                                    event.target.value
                                        ? Number(
                                            event.target.value,
                                        )
                                        : null,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            >
                                <option value="">
                                    Select Product
                                </option>

                                {products.map(
                                    (product) => (
                                        <option
                                            key={product.id}
                                            value={product.id}
                                        >
                                            {product.name}
                                        </option>
                                    ),
                                )}
                            </select>
                        </FormField>

                        <FormField
                            label="Catalog Code"
                            htmlFor="catalog_code"
                            error={
                                form.errors.catalog_code
                            }
                            required
                        >
                            <input
                                id="catalog_code"
                                value={
                                    form.data.catalog_code
                                }
                                onChange={(event) => form.setData(
                                    'catalog_code',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Procurement Title"
                            htmlFor="procurement_title"
                            error={
                                form.errors
                                    .procurement_title
                            }
                            required
                        >
                            <input
                                id="procurement_title"
                                value={
                                    form.data
                                        .procurement_title
                                }
                                onChange={(event) => form.setData(
                                    'procurement_title',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Procurement Category"
                            htmlFor="procurement_category"
                        >
                            <input
                                id="procurement_category"
                                value={
                                    form.data
                                        .procurement_category
                                }
                                onChange={(event) => form.setData(
                                    'procurement_category',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Availability"
                            htmlFor="availability"
                            required
                        >
                            <select
                                id="availability"
                                value={
                                    form.data.availability
                                }
                                onChange={(event) => form.setData(
                                    'availability',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            >
                                {availabilities.map(
                                    (value) => (
                                        <option
                                            key={value}
                                            value={value}
                                        >
                                            {value}
                                        </option>
                                    ),
                                )}
                            </select>
                        </FormField>

                        <FormField
                            label="Unit / Package"
                            htmlFor="unit_package"
                        >
                            <input
                                id="unit_package"
                                value={
                                    form.data.unit_package
                                }
                                onChange={(event) => form.setData(
                                    'unit_package',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Effective At"
                            htmlFor="effective_at"
                        >
                            <input
                                id="effective_at"
                                type="datetime-local"
                                value={
                                    form.data.effective_at
                                    ?? ''
                                }
                                onChange={(event) => form.setData(
                                    'effective_at',
                                    event.target.value
                                    || null,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Expires At"
                            htmlFor="expires_at"
                            error={
                                form.errors.expires_at
                            }
                        >
                            <input
                                id="expires_at"
                                type="datetime-local"
                                value={
                                    form.data.expires_at
                                    ?? ''
                                }
                                onChange={(event) => form.setData(
                                    'expires_at',
                                    event.target.value
                                    || null,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Reference URL"
                            htmlFor="reference_url"
                            error={
                                form.errors.reference_url
                            }
                        >
                            <input
                                id="reference_url"
                                type="url"
                                value={
                                    form.data.reference_url
                                }
                                onChange={(event) => form.setData(
                                    'reference_url',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Sort Order"
                            htmlFor="sort_order"
                        >
                            <input
                                id="sort_order"
                                type="number"
                                min={0}
                                value={
                                    form.data.sort_order
                                }
                                onChange={(event) => form.setData(
                                    'sort_order',
                                    Number(
                                        event.target.value,
                                    ),
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Notes"
                        htmlFor="notes"
                    >
                        <textarea
                            id="notes"
                            value={form.data.notes}
                            onChange={(event) => form.setData(
                                'notes',
                                event.target.value,
                            )}
                            className="min-h-28 w-full rounded-lg border border-input px-3 py-2"
                        />
                    </FormField>

                    <div className="grid gap-5 md:grid-cols-2">
                        <FormField
                            label="SEO Title"
                            htmlFor="seo_title"
                        >
                            <input
                                id="seo_title"
                                value={
                                    form.data.seo_title
                                }
                                onChange={(event) => form.setData(
                                    'seo_title',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="SEO Description"
                            htmlFor="seo_description"
                        >
                            <textarea
                                id="seo_description"
                                value={
                                    form.data
                                        .seo_description
                                }
                                onChange={(event) => form.setData(
                                    'seo_description',
                                    event.target.value,
                                )}
                                className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </div>

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            {form.processing
                                ? 'Saving...'
                                : 'Save'}
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}