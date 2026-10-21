// path: resources/js/pages/Admin/Activities/Form.tsx

import {
    Head,
    router,
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    ActivityBlockEditor,
    type ActivityBlock,
} from '@/components/activities/ActivityBlockEditor';

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

type Option = {
    id: number;
    name: string;
};

type MediaOption = Option & {
    url: string;
};

type FormData = {
    expected_lock_version: number | null;
    requested_slug: string;
    title: string;
    excerpt: string;
    category_id: number | null;
    featured_media_id: number | null;
    is_featured: boolean;
    seo_title: string;
    seo_description: string;
    og_media_id: number | null;
    blocks: ActivityBlock[];
    related_product_ids: number[];
};

type Props = {
    activity: {
        id: number;
        status: string;
        lock_version: number;
    } | null;

    form: FormData;

    options: {
        categories: Option[];
        media: MediaOption[];
        products: Option[];
    };

    can?: {
        publish: boolean;
        archive: boolean;
    };
};

export default function Form({
    activity,
    form: initial,
    options,
    can,
}: Props) {
    const form = useForm<FormData>(
        initial,
    );

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        if (activity === null) {
            form.post('/admin/activities');

            return;
        }

        form.put(
            `/admin/activities/${activity.id}`,
        );
    }

    return (
        <>
            <Head
                title={
                    activity
                        ? 'Edit Activity'
                        : 'Create Activity'
                }
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Activities',
                        href: '/admin/activities',
                    },
                    {
                        label: activity
                            ? 'Edit'
                            : 'Create',
                    },
                ]}
            >
                <PageHeader
                    title={
                        activity
                            ? 'Edit Activity'
                            : 'Create Activity'
                    }
                    description={
                        activity
                            ? `Status: ${activity.status}`
                            : 'WORKING revision is created first.'
                    }
                    actions={
                        activity && can ? (
                            <div className="flex gap-2">
                                {can.publish && (
                                    <Button
                                        type="button"
                                        onClick={() => router.post(
                                            `/admin/activities/${activity.id}/publish`,
                                            {
                                                lock_version:
                                                    activity
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
                                            `/admin/activities/${activity.id}/archive`,
                                            {
                                                lock_version:
                                                    activity
                                                        .lock_version,
                                            },
                                        )}
                                    >
                                        Archive
                                    </Button>
                                )}
                            </div>
                        ) : undefined
                    }
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    <section className="rounded-2xl border bg-background p-6">
                        <div className="grid gap-5 md:grid-cols-2">
                            <FormField
                                label="Title"
                                htmlFor="title"
                                error={
                                    form.errors.title
                                }
                                required
                            >
                                <input
                                    id="title"
                                    value={
                                        form.data.title
                                    }
                                    onChange={(event) => form.setData(
                                        'title',
                                        event.target.value,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Requested Slug"
                                htmlFor="requested_slug"
                                error={
                                    form.errors
                                        .requested_slug
                                }
                                required
                            >
                                <input
                                    id="requested_slug"
                                    value={
                                        form.data
                                            .requested_slug
                                    }
                                    onChange={(event) => form.setData(
                                        'requested_slug',
                                        event.target.value,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Category"
                                htmlFor="category_id"
                            >
                                <select
                                    id="category_id"
                                    value={
                                        form.data
                                            .category_id
                                        ?? ''
                                    }
                                    onChange={(event) => form.setData(
                                        'category_id',
                                        event.target.value
                                            ? Number(
                                                event
                                                    .target
                                                    .value,
                                            )
                                            : null,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                >
                                    <option value="">
                                        None
                                    </option>

                                    {options.categories.map(
                                        (category) => (
                                            <option
                                                key={
                                                    category.id
                                                }
                                                value={
                                                    category.id
                                                }
                                            >
                                                {
                                                    category.name
                                                }
                                            </option>
                                        ),
                                    )}
                                </select>
                            </FormField>

                            <FormField
                                label="Featured Image"
                                htmlFor="featured_media_id"
                            >
                                <select
                                    id="featured_media_id"
                                    value={
                                        form.data
                                            .featured_media_id
                                        ?? ''
                                    }
                                    onChange={(event) => form.setData(
                                        'featured_media_id',
                                        event.target.value
                                            ? Number(
                                                event
                                                    .target
                                                    .value,
                                            )
                                            : null,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                >
                                    <option value="">
                                        None
                                    </option>

                                    {options.media.map(
                                        (media) => (
                                            <option
                                                key={
                                                    media.id
                                                }
                                                value={
                                                    media.id
                                                }
                                            >
                                                {
                                                    media.name
                                                }
                                            </option>
                                        ),
                                    )}
                                </select>
                            </FormField>
                        </div>

                        <FormField
                            label="Excerpt"
                            htmlFor="excerpt"
                        >
                            <textarea
                                id="excerpt"
                                value={
                                    form.data.excerpt
                                }
                                onChange={(event) => form.setData(
                                    'excerpt',
                                    event.target.value,
                                )}
                                className="mt-5 min-h-24 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <label className="mt-5 flex items-center gap-3 text-sm font-medium">
                            <input
                                type="checkbox"
                                checked={
                                    form.data
                                        .is_featured
                                }
                                onChange={(event) => form.setData(
                                    'is_featured',
                                    event.target.checked,
                                )}
                            />

                            Featured Activity
                        </label>
                    </section>

                    <ActivityBlockEditor
                        blocks={form.data.blocks}
                        media={options.media}
                        products={options.products}
                        onChange={(blocks) => form.setData(
                            'blocks',
                            blocks,
                        )}
                    />

                    <section className="rounded-2xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            Related Products
                        </h2>

                        <select
                            multiple
                            value={
                                form.data
                                    .related_product_ids
                                    .map(String)
                            }
                            onChange={(event) => form.setData(
                                'related_product_ids',
                                Array.from(
                                    event
                                        .currentTarget
                                        .selectedOptions,
                                ).map(
                                    (option) =>
                                        Number(
                                            option.value,
                                        ),
                                ),
                            )}
                            className="mt-4 min-h-36 w-full rounded-lg border border-input px-3 py-2"
                        >
                            {options.products.map(
                                (product) => (
                                    <option
                                        key={
                                            product.id
                                        }
                                        value={
                                            product.id
                                        }
                                    >
                                        {product.name}
                                    </option>
                                ),
                            )}
                        </select>
                    </section>

                    <section className="rounded-2xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            SEO
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField
                                label="SEO Title"
                                htmlFor="seo_title"
                            >
                                <input
                                    id="seo_title"
                                    value={
                                        form.data
                                            .seo_title
                                    }
                                    onChange={(event) => form.setData(
                                        'seo_title',
                                        event.target.value,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="OG Image"
                                htmlFor="og_media_id"
                            >
                                <select
                                    id="og_media_id"
                                    value={
                                        form.data
                                            .og_media_id
                                        ?? ''
                                    }
                                    onChange={(event) => form.setData(
                                        'og_media_id',
                                        event.target.value
                                            ? Number(
                                                event
                                                    .target
                                                    .value,
                                            )
                                            : null,
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                >
                                    <option value="">
                                        None
                                    </option>

                                    {options.media.map(
                                        (media) => (
                                            <option
                                                key={
                                                    media.id
                                                }
                                                value={
                                                    media.id
                                                }
                                            >
                                                {
                                                    media.name
                                                }
                                            </option>
                                        ),
                                    )}
                                </select>
                            </FormField>
                        </div>

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
                                className="mt-5 min-h-24 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </section>

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            Save Draft
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}