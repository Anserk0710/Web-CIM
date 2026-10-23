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
import {
    humanizeStatus,
} from '@/lib/humanize';

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
                        ? 'Ubah Berita & Kegiatan'
                        : 'Tulis Berita & Kegiatan Baru'
                }
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Berita & Kegiatan',
                        href: '/admin/activities',
                    },
                    {
                        label: activity
                            ? 'Ubah'
                            : 'Tulis Baru',
                    },
                ]}
            >
                <PageHeader
                    title={
                        activity
                            ? 'Ubah Berita & Kegiatan'
                            : 'Tulis Berita & Kegiatan Baru'
                    }
                    description={
                        activity
                            ? `Status Saat Ini: ${humanizeStatus(activity.status)}`
                            : 'Draf artikel akan disimpan terlebih dahulu sebelum diterbitkan.'
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
                                        Terbitkan
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
                                        Arsipkan
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
                                label="Judul Berita / Kegiatan"
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
                                    placeholder="Tuliskan judul berita atau kegiatan..."
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Tautan Halaman (Slug)"
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
                                    placeholder="judul-berita-kegiatan"
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Kategori Berita"
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
                                        Pilih Kategori (Opsional)
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
                                label="Gambar Utama (Sampul)"
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
                                        Pilih Gambar Sampul (Opsional)
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
                            label="Ringkasan Singkat (Lead)"
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
                                placeholder="Tuliskan 1-2 kalimat ringkasan pembuka artikel untuk ditampilkan pada daftar berita..."
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

                            Tampilkan Sebagai Berita Utama / Sorotan di Halaman Depan
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
                            Produk Terkait
                        </h2>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Pilih produk alat kesehatan yang relevan dengan dokumentasi kegiatan ini.
                        </p>

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
                            Pengaturan SEO & Pratinjau Media Sosial
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField
                                label="Judul Penelusuran (SEO Title)"
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
                                    placeholder="Judul ringkas untuk hasil pencarian Google"
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Gambar Pratinjau Media Sosial (OG Image)"
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
                                        Pilih Gambar Pratinjau (Opsional)
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
                            label="Deskripsi Penelusuran (Meta Description)"
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
                                placeholder="Ringkasan informatif 150-160 karakter untuk ditampilkan di hasil pencarian..."
                                className="mt-5 min-h-24 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </section>

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            {form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Draf'}
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}