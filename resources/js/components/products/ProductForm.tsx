import {
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import {
    type GalleryItem,
    type ProductFormData,
    type ProductOptions,
    type SpecificationGroup,
} from '@/types/product';

type Props = {
    initial: ProductFormData;
    options: ProductOptions;
    action: string;
    method: 'post' | 'put';
    submitLabel: string;
};

export function ProductForm({
    initial,
    options,
    action,
    method,
    submitLabel,
}: Props) {
    const form = useForm<ProductFormData>(
        initial,
    );

    const images = options.media.filter(
        (media) => media.mime.startsWith(
            'image/',
        ),
    );

    const pdfs = options.media.filter(
        (media) => media.mime
            === 'application/pdf',
    );

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        if (method === 'post') {
            form.post(action);

            return;
        }

        form.put(action);
    }

    function selectedGalleryIds(): string[] {
        return form.data.gallery.map(
            (item) => String(item.media_id),
        );
    }

    function changeGallery(
        ids: number[],
    ) {
        const existing = new Map(
            form.data.gallery.map(
                (item) => [
                    item.media_id,
                    item,
                ],
            ),
        );

        form.setData(
            'gallery',
            ids.map(
                (id): GalleryItem => existing
                    .get(id)
                    ?? {
                    media_id: id,
                    alt_text: '',
                    caption: '',
                },
            ),
        );
    }

    function updateGallery(
        index: number,
        patch: Partial<GalleryItem>,
    ) {
        const next = [
            ...form.data.gallery,
        ];

        next[index] = {
            ...next[index],
            ...patch,
        };

        form.setData(
            'gallery',
            next,
        );
    }

    function updateSpecGroup(
        index: number,
        group: SpecificationGroup,
    ) {
        const next = [
            ...form.data.spec_groups,
        ];

        next[index] = group;

        form.setData(
            'spec_groups',
            next,
        );
    }

    return (
        <form
            onSubmit={submit}
            className="mt-8 space-y-8"
        >
            {Object.keys(form.errors).length > 0 && (
                <div className="rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive">
                    Periksa kembali data formulir yang belum sesuai.
                </div>
            )}

            <section className="rounded-2xl border bg-background p-6">
                <h2 className="font-semibold text-cim-navy">
                    Informasi Dasar Produk
                </h2>

                <div className="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="name"
                            className="mb-2 block text-sm font-medium"
                        >
                            Nama Produk
                        </label>

                        <input
                            id="name"
                            value={form.data.name}
                            onChange={(event) => form.setData(
                                'name',
                                event.target.value,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                            required
                        />

                        {form.errors.name && (
                            <p className="mt-2 text-sm text-destructive">
                                {form.errors.name}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="requested_slug"
                            className="mb-2 block text-sm font-medium"
                        >
                            Tautan / Slug Halaman
                        </label>

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
                            required
                        />

                        {form.errors.requested_slug && (
                            <p className="mt-2 text-sm text-destructive">
                                {
                                    form.errors
                                        .requested_slug
                                }
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="category_id"
                            className="mb-2 block text-sm font-medium"
                        >
                            Kategori Utama
                        </label>

                        <select
                            id="category_id"
                            value={
                                form.data.category_id
                                ?? ''
                            }
                            onChange={(event) => form.setData(
                                'category_id',
                                event.target.value
                                    ? Number(
                                        event.target.value,
                                    )
                                    : null,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                            required
                        >
                            <option value="">
                                Pilih Kategori
                            </option>

                            {options.categories.map(
                                (option) => (
                                    <option
                                        key={option.id}
                                        value={option.id}
                                    >
                                        {option.name}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>

                    <div>
                        <label
                            htmlFor="brand_id"
                            className="mb-2 block text-sm font-medium"
                        >
                            Merek / Brand
                        </label>

                        <select
                            id="brand_id"
                            value={
                                form.data.brand_id
                                ?? ''
                            }
                            onChange={(event) => form.setData(
                                'brand_id',
                                event.target.value
                                    ? Number(
                                        event.target.value,
                                    )
                                    : null,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                        >
                            <option value="">
                                Tanpa Merek
                            </option>

                            {options.brands.map(
                                (option) => (
                                    <option
                                        key={option.id}
                                        value={option.id}
                                    >
                                        {option.name}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>

                    <div>
                        <label
                            htmlFor="type_id"
                            className="mb-2 block text-sm font-medium"
                        >
                            Tipe Produk
                        </label>

                        <select
                            id="type_id"
                            value={
                                form.data.type_id
                                ?? ''
                            }
                            onChange={(event) => form.setData(
                                'type_id',
                                event.target.value
                                    ? Number(
                                        event.target.value,
                                    )
                                    : null,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                        >
                            <option value="">
                                Tanpa Tipe
                            </option>

                            {options.types.map(
                                (option) => (
                                    <option
                                        key={option.id}
                                        value={option.id}
                                    >
                                        {option.name}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>

                    <label className="flex items-center gap-3 self-end pb-2 text-sm font-medium">
                        <input
                            type="checkbox"
                            checked={
                                form.data.is_featured
                            }
                            onChange={(event) => form.setData(
                                'is_featured',
                                event.target.checked,
                            )}
                        />

                        Tampilkan sebagai Produk Unggulan
                    </label>
                </div>

                <div className="mt-5">
                    <label
                        htmlFor="summary"
                        className="mb-2 block text-sm font-medium"
                    >
                        Ringkasan Singkat
                    </label>

                    <textarea
                        id="summary"
                        value={form.data.summary}
                        onChange={(event) => form.setData(
                            'summary',
                            event.target.value,
                        )}
                        className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                    />
                </div>

                <div className="mt-5">
                    <label
                        htmlFor="description"
                        className="mb-2 block text-sm font-medium"
                    >
                        Deskripsi Lengkap
                    </label>

                    <textarea
                        id="description"
                        value={
                            form.data.description
                        }
                        onChange={(event) => form.setData(
                            'description',
                            event.target.value,
                        )}
                        className="min-h-40 w-full rounded-lg border border-input px-3 py-2"
                    />
                </div>
            </section>

            <section className="rounded-2xl border bg-background p-6">
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-cim-navy">
                        Keunggulan Utama Produk
                    </h2>

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => form.setData(
                            'highlights',
                            [
                                ...form.data.highlights,
                                '',
                            ],
                        )}
                    >
                        Tambah Poin Keunggulan
                    </Button>
                </div>

                <div className="mt-5 space-y-3">
                    {form.data.highlights.map(
                        (highlight, index) => (
                            <div
                                key={index}
                                className="flex gap-2"
                            >
                                <input
                                    aria-label={`Poin Keunggulan ${index + 1}`}
                                    placeholder="Contoh: Akurasi pengukuran tinggi dan bergaransi resmi"
                                    value={highlight}
                                    onChange={(event) => {
                                        const next = [
                                            ...form.data
                                                .highlights,
                                        ];

                                        next[index] =
                                            event.target
                                                .value;

                                        form.setData(
                                            'highlights',
                                            next,
                                        );
                                    }}
                                    className="flex-1 rounded-lg border border-input px-3 py-2"
                                />

                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => form.setData(
                                        'highlights',
                                        form.data.highlights
                                            .filter(
                                                (_, itemIndex) =>
                                                    itemIndex
                                                    !== index,
                                            ),
                                    )}
                                >
                                    Hapus
                                </Button>
                            </div>
                        ),
                    )}
                </div>
            </section>

            <section className="rounded-2xl border bg-background p-6">
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-cim-navy">
                        Spesifikasi Teknis
                    </h2>

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => form.setData(
                            'spec_groups',
                            [
                                ...form.data.spec_groups,
                                {
                                    name: '',
                                    items: [],
                                },
                            ],
                        )}
                    >
                        Tambah Kelompok
                    </Button>
                </div>

                <div className="mt-5 space-y-5">
                    {form.data.spec_groups.map(
                        (group, groupIndex) => (
                            <div
                                key={groupIndex}
                                className="rounded-xl border p-4"
                            >
                                <div className="flex gap-2">
                                    <input
                                        aria-label={`Kelompok Spesifikasi ${groupIndex + 1}`}
                                        placeholder="Nama Kelompok (contoh: Dimensi & Daya)"
                                        value={group.name}
                                        onChange={(event) => updateSpecGroup(
                                            groupIndex,
                                            {
                                                ...group,
                                                name: event
                                                    .target
                                                    .value,
                                            },
                                        )}
                                        className="flex-1 rounded-lg border border-input px-3 py-2"
                                    />

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => form.setData(
                                            'spec_groups',
                                            form.data.spec_groups
                                                .filter(
                                                    (
                                                        _,
                                                        index,
                                                    ) =>
                                                        index
                                                        !== groupIndex,
                                                ),
                                        )}
                                    >
                                        Hapus Kelompok
                                    </Button>
                                </div>

                                <div className="mt-4 space-y-3">
                                    {group.items.map(
                                        (
                                            item,
                                            itemIndex,
                                        ) => (
                                            <div
                                                key={
                                                    itemIndex
                                                }
                                                className="grid gap-2 md:grid-cols-[1fr_1fr_120px_auto]"
                                            >
                                                <input
                                                    placeholder="Nama Fitur / Parameter"
                                                    value={
                                                        item.label
                                                    }
                                                    onChange={(event) => {
                                                        const items = [
                                                            ...group
                                                                .items,
                                                        ];

                                                        items[
                                                            itemIndex
                                                        ] = {
                                                            ...item,
                                                            label: event
                                                                .target
                                                                .value,
                                                        };

                                                        updateSpecGroup(
                                                            groupIndex,
                                                            {
                                                                ...group,
                                                                items,
                                                            },
                                                        );
                                                    }}
                                                    className="rounded-lg border border-input px-3 py-2"
                                                />

                                                <input
                                                    placeholder="Nilai / Spesifikasi"
                                                    value={
                                                        item.value
                                                    }
                                                    onChange={(event) => {
                                                        const items = [
                                                            ...group
                                                                .items,
                                                        ];

                                                        items[
                                                            itemIndex
                                                        ] = {
                                                            ...item,
                                                            value: event
                                                                .target
                                                                .value,
                                                        };

                                                        updateSpecGroup(
                                                            groupIndex,
                                                            {
                                                                ...group,
                                                                items,
                                                            },
                                                        );
                                                    }}
                                                    className="rounded-lg border border-input px-3 py-2"
                                                />

                                                <input
                                                    placeholder="Satuan (opsional)"
                                                    value={
                                                        item.unit
                                                    }
                                                    onChange={(event) => {
                                                        const items = [
                                                            ...group
                                                                .items,
                                                        ];

                                                        items[
                                                            itemIndex
                                                        ] = {
                                                            ...item,
                                                            unit: event
                                                                .target
                                                                .value,
                                                        };

                                                        updateSpecGroup(
                                                            groupIndex,
                                                            {
                                                                ...group,
                                                                items,
                                                            },
                                                        );
                                                    }}
                                                    className="rounded-lg border border-input px-3 py-2"
                                                />

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() => updateSpecGroup(
                                                        groupIndex,
                                                        {
                                                            ...group,
                                                            items: group
                                                                .items
                                                                .filter(
                                                                    (
                                                                        _,
                                                                        index,
                                                                    ) =>
                                                                        index
                                                                        !== itemIndex,
                                                                ),
                                                        },
                                                    )}
                                                >
                                                    Hapus
                                                </Button>
                                            </div>
                                        ),
                                    )}
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="mt-4"
                                    onClick={() => updateSpecGroup(
                                        groupIndex,
                                        {
                                            ...group,
                                            items: [
                                                ...group.items,
                                                {
                                                    label: '',
                                                    value: '',
                                                    unit: '',
                                                },
                                            ],
                                        },
                                    )}
                                >
                                    Tambah Baris Spesifikasi
                                </Button>
                            </div>
                        ),
                    )}
                </div>
            </section>

            <section className="rounded-2xl border bg-background p-6">
                <h2 className="font-semibold text-cim-navy">
                    Media Foto & Brosur
                </h2>

                <div className="mt-5">
                    <label className="mb-2 block text-sm font-medium">
                        Galeri Foto Produk
                    </label>

                    <select
                        multiple
                        value={selectedGalleryIds()}
                        onChange={(event) => changeGallery(
                            Array.from(
                                event.currentTarget
                                    .selectedOptions,
                            ).map(
                                (option) => Number(
                                    option.value,
                                ),
                            ),
                        )}
                        className="min-h-40 w-full rounded-lg border border-input px-3 py-2"
                    >
                        {images.map((media) => (
                            <option
                                key={media.id}
                                value={media.id}
                            >
                                {media.name}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="mt-4 space-y-4">
                    {form.data.gallery.map(
                        (gallery, index) => {
                            const media = images.find(
                                (item) =>
                                    item.id
                                    === gallery.media_id,
                            );

                            return (
                                <div
                                    key={gallery.media_id}
                                    className="grid gap-3 rounded-xl border p-4 md:grid-cols-[100px_1fr_1fr]"
                                >
                                    {media && (
                                        <img
                                            src={media.url}
                                            alt=""
                                            className="size-24 rounded-lg object-cover"
                                        />
                                    )}

                                    <input
                                        placeholder="Keterangan teks gambar (Alt text)"
                                        value={
                                            gallery.alt_text
                                        }
                                        onChange={(event) => updateGallery(
                                            index,
                                            {
                                                alt_text:
                                                    event
                                                        .target
                                                        .value,
                                            },
                                        )}
                                        className="rounded-lg border border-input px-3 py-2"
                                    />

                                    <input
                                        placeholder="Keterangan foto (Caption)"
                                        value={
                                            gallery.caption
                                        }
                                        onChange={(event) => updateGallery(
                                            index,
                                            {
                                                caption:
                                                    event
                                                        .target
                                                        .value,
                                            },
                                        )}
                                        className="rounded-lg border border-input px-3 py-2"
                                    />
                                </div>
                            );
                        },
                    )}
                </div>

                <div className="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="brochure_media_id"
                            className="mb-2 block text-sm font-medium"
                        >
                            Berkas Brosur (PDF)
                        </label>

                        <select
                            id="brochure_media_id"
                            value={
                                form.data
                                    .brochure_media_id
                                ?? ''
                            }
                            onChange={(event) => form.setData(
                                'brochure_media_id',
                                event.target.value
                                    ? Number(
                                        event.target.value,
                                    )
                                    : null,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                        >
                            <option value="">
                                Tanpa Berkas Brosur
                            </option>

                            {pdfs.map((media) => (
                                <option
                                    key={media.id}
                                    value={media.id}
                                >
                                    {media.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label
                            htmlFor="og_media_id"
                            className="mb-2 block text-sm font-medium"
                        >
                            Gambar Pratinjau Sosial (OG Image)
                        </label>

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
                                        event.target.value,
                                    )
                                    : null,
                            )}
                            className="w-full rounded-lg border border-input px-3 py-2"
                        >
                            <option value="">
                                Tanpa Gambar Pratinjau
                            </option>

                            {images.map((media) => (
                                <option
                                    key={media.id}
                                    value={media.id}
                                >
                                    {media.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </section>

            <section className="rounded-2xl border bg-background p-6">
                <h2 className="font-semibold text-cim-navy">
                    Produk Terkait
                </h2>

                <select
                    multiple
                    value={form.data.related_product_ids.map(
                        String,
                    )}
                    onChange={(event) => form.setData(
                        'related_product_ids',
                        Array.from(
                            event.currentTarget
                                .selectedOptions,
                        ).map(
                            (option) => Number(
                                option.value,
                            ),
                        ),
                    )}
                    className="mt-5 min-h-32 w-full rounded-lg border border-input px-3 py-2"
                >
                    {options.products.map(
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
            </section>

            <section className="rounded-2xl border bg-background p-6">
                <h2 className="font-semibold text-cim-navy">
                    Optimasi Mesin Pencari (SEO)
                </h2>

                <div className="mt-5 space-y-5">
                    <div>
                        <label
                            htmlFor="seo_title"
                            className="mb-2 block text-sm font-medium"
                        >
                            Judul Meta (SEO Title)
                        </label>

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
                    </div>

                    <div>
                        <label
                            htmlFor="seo_description"
                            className="mb-2 block text-sm font-medium"
                        >
                            Deskripsi Meta (SEO Description)
                        </label>

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
                    </div>
                </div>
            </section>

            <div className="flex justify-end">
                <Button
                    type="submit"
                    disabled={form.processing}
                >
                    {form.processing
                        ? 'Menyimpan...'
                        : submitLabel}
                </Button>
            </div>
        </form>
    );
}