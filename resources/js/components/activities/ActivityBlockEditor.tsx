import {
    Button,
} from '@/components/ui/button';

type MediaOption = {
    id: number;
    name: string;
    url: string;
};

type ProductOption = {
    id: number;
    name: string;
};

export type ActivityBlock = {
    block_key: string;
    type: string;
    data: Record<string, string | number | number[] | null>;
};

type Props = {
    blocks: ActivityBlock[];
    media: MediaOption[];
    products: ProductOption[];
    onChange: (blocks: ActivityBlock[]) => void;
};

function newBlock(
    type: string,
): ActivityBlock {
    const data: ActivityBlock['data'] =
        type === 'heading'
            ? {
                text: '',
                level: 2,
            }
            : type === 'gallery'
                ? {
                    media_ids: [],
                    caption: '',
                }
                : type === 'image'
                    ? {
                        media_id: null,
                        alt: '',
                        caption: '',
                    }
                    : type === 'related_product'
                        ? {
                            product_id: null,
                        }
                        : type === 'divider'
                            ? {}
                            : {
                                text: '',
                            };

    return {
        block_key:
            crypto.randomUUID(),
        type,
        data,
    };
}

const BLOCK_LABELS: Record<string, string> = {
    paragraph: 'Paragraf Teks',
    heading: 'Subjudul',
    image: 'Gambar Tunggal',
    gallery: 'Galeri Foto',
    quote: 'Kutipan Teks',
    video: 'Tautan Video',
    cta: 'Tombol Aksi (CTA)',
    related_product: 'Produk Terkait',
    divider: 'Garis Pembatas',
};

const BLOCK_BUTTONS = [
    { type: 'paragraph', label: '+ Paragraf' },
    { type: 'heading', label: '+ Subjudul' },
    { type: 'image', label: '+ Gambar' },
    { type: 'gallery', label: '+ Galeri' },
    { type: 'quote', label: '+ Kutipan' },
    { type: 'video', label: '+ Video' },
    { type: 'cta', label: '+ Tombol Aksi' },
    { type: 'related_product', label: '+ Produk Terkait' },
    { type: 'divider', label: '+ Pembatas' },
];

export function ActivityBlockEditor({
    blocks,
    media,
    products,
    onChange,
}: Props) {
    function patch(
        index: number,
        patch: Partial<ActivityBlock>,
    ) {
        const next = [...blocks];

        next[index] = {
            ...next[index],
            ...patch,
        };

        onChange(next);
    }

    function patchData(
        index: number,
        key: string,
        value: ActivityBlock['data'][string],
    ) {
        const block = blocks[index];

        patch(
            index,
            {
                data: {
                    ...block.data,
                    [key]: value,
                },
            },
        );
    }

    function move(
        index: number,
        direction: -1 | 1,
    ) {
        const target =
            index + direction;

        if (
            target < 0
            || target >= blocks.length
        ) {
            return;
        }

        const next = [...blocks];

        [
            next[index],
            next[target],
        ] = [
                next[target],
                next[index],
            ];

        onChange(next);
    }

    function remove(
        index: number,
    ) {
        onChange(
            blocks.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        );
    }

    return (
        <section className="rounded-2xl border bg-background p-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 className="font-semibold text-cim-navy">
                        Blok Konten Artikel
                    </h2>

                    <p className="mt-1 text-xs text-muted-foreground">
                        Maksimal 100 blok konten. Penataan artikel menggunakan blok visual terstruktur.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {BLOCK_BUTTONS.map((item) => (
                        <Button
                            key={item.type}
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => onChange([
                                ...blocks,
                                newBlock(item.type),
                            ])}
                        >
                            {item.label}
                        </Button>
                    ))}
                </div>
            </div>

            <div className="mt-6 space-y-4">
                {blocks.map(
                    (block, index) => (
                        <article
                            key={block.block_key}
                            className="rounded-xl border p-4"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <strong className="text-sm font-semibold text-cim-navy">
                                    {BLOCK_LABELS[block.type] ?? block.type}
                                </strong>

                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        disabled={
                                            index === 0
                                        }
                                        onClick={() =>
                                            move(
                                                index,
                                                -1,
                                            )
                                        }
                                    >
                                        ↑
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        disabled={
                                            index
                                            === blocks.length
                                            - 1
                                        }
                                        onClick={() =>
                                            move(
                                                index,
                                                1,
                                            )
                                        }
                                    >
                                        ↓
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onClick={() =>
                                            remove(
                                                index,
                                            )
                                        }
                                    >
                                        Hapus Blok
                                    </Button>
                                </div>
                            </div>

                            {[
                                'paragraph',
                                'quote',
                            ].includes(
                                block.type,
                            ) && (
                                    <textarea
                                        value={
                                            String(
                                                block
                                                    .data
                                                    .text
                                                ?? '',
                                            )
                                        }
                                        onChange={(event) =>
                                            patchData(
                                                index,
                                                'text',
                                                event.target
                                                    .value,
                                            )
                                        }
                                        placeholder="Tuliskan isi teks atau kutipan di sini..."
                                        className="mt-4 min-h-28 w-full rounded-lg border border-input px-3 py-2"
                                    />
                                )}

                            {block.type
                                === 'heading' && (
                                    <div className="mt-4 grid gap-3 sm:grid-cols-[140px_1fr]">
                                        <select
                                            value={
                                                Number(
                                                    block
                                                        .data
                                                        .level
                                                    ?? 2,
                                                )
                                            }
                                            onChange={(event) =>
                                                patchData(
                                                    index,
                                                    'level',
                                                    Number(
                                                        event
                                                            .target
                                                            .value,
                                                    ),
                                                )
                                            }
                                            className="rounded-lg border border-input px-3 py-2 text-sm"
                                        >
                                            <option value={2}>
                                                Subjudul H2
                                            </option>

                                            <option value={3}>
                                                Subjudul H3
                                            </option>

                                            <option value={4}>
                                                Subjudul H4
                                            </option>
                                        </select>

                                        <input
                                            value={
                                                String(
                                                    block
                                                        .data
                                                        .text
                                                    ?? '',
                                                )
                                            }
                                            onChange={(event) =>
                                                patchData(
                                                    index,
                                                    'text',
                                                    event.target
                                                        .value,
                                                )
                                            }
                                            placeholder="Tuliskan teks subjudul..."
                                            className="rounded-lg border border-input px-3 py-2"
                                        />
                                    </div>
                                )}

                            {block.type
                                === 'image' && (
                                    <div className="mt-4 grid gap-3 md:grid-cols-3">
                                        <select
                                            value={
                                                String(
                                                    block
                                                        .data
                                                        .media_id
                                                    ?? '',
                                                )
                                            }
                                            onChange={(event) =>
                                                patchData(
                                                    index,
                                                    'media_id',
                                                    Number(
                                                        event
                                                            .target
                                                            .value,
                                                    ),
                                                )
                                            }
                                            className="rounded-lg border border-input px-3 py-2"
                                        >
                                            <option value="">
                                                Pilih Gambar dari Media
                                            </option>

                                            {media.map(
                                                (item) => (
                                                    <option
                                                        key={
                                                            item.id
                                                        }
                                                        value={
                                                            item.id
                                                        }
                                                    >
                                                        {
                                                            item.name
                                                        }
                                                    </option>
                                                ),
                                            )}
                                        </select>

                                        <input
                                            value={
                                                String(
                                                    block
                                                        .data
                                                        .alt
                                                    ?? '',
                                                )
                                            }
                                            onChange={(event) =>
                                                patchData(
                                                    index,
                                                    'alt',
                                                    event.target
                                                        .value,
                                                )
                                            }
                                            placeholder="Deskripsi gambar (alt text)"
                                            className="rounded-lg border border-input px-3 py-2"
                                        />

                                        <input
                                            value={
                                                String(
                                                    block
                                                        .data
                                                        .caption
                                                    ?? '',
                                                )
                                            }
                                            onChange={(event) =>
                                                patchData(
                                                    index,
                                                    'caption',
                                                    event.target
                                                        .value,
                                                )
                                            }
                                            placeholder="Keterangan gambar (kapsi)"
                                            className="rounded-lg border border-input px-3 py-2"
                                        />
                                    </div>
                                )}

                            {block.type
                                === 'gallery' && (
                                    <div className="mt-4">
                                        <select
                                            multiple
                                            value={
                                                Array.isArray(block.data.media_ids)
                                                    ? block.data.media_ids.map(String)
                                                    : []
                                            }
                                        onChange={(event) =>
                                            patchData(
                                                index,
                                                'media_ids',
                                                Array.from(
                                                    event
                                                        .currentTarget
                                                        .selectedOptions,
                                                ).map(
                                                    (
                                                        option,
                                                    ) =>
                                                        Number(
                                                            option
                                                                .value,
                                                        ),
                                                ),
                                            )
                                        }
                                        className="min-h-36 w-full rounded-lg border border-input px-3 py-2"
                                    >
                                        {media.map(
                                            (item) => (
                                                <option
                                                    key={
                                                        item.id
                                                    }
                                                    value={
                                                        item.id
                                                    }
                                                >
                                                    {
                                                        item.name
                                                    }
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </div>
                    )}

                {block.type
                    === 'related_product' && (
                        <select
                            value={
                                String(
                                    block
                                        .data
                                        .product_id
                                    ?? '',
                                )
                            }
                            onChange={(event) =>
                                patchData(
                                    index,
                                    'product_id',
                                    Number(
                                        event
                                            .target
                                            .value,
                                    ),
                                )
                            }
                            className="mt-4 w-full rounded-lg border border-input px-3 py-2"
                        >
                            <option value="">
                                Pilih Produk Terkait
                            </option>

                            {products.map(
                                (product) => (
                                    <option
                                        key={
                                            product.id
                                        }
                                        value={
                                            product.id
                                        }
                                    >
                                        {
                                            product.name
                                        }
                                    </option>
                                ),
                            )}
                        </select>
                    )}

                {block.type
                    === 'video' && (
                        <input
                            value={
                                String(
                                    block
                                        .data
                                        .url
                                    ?? '',
                                )
                            }
                            onChange={(event) =>
                                patchData(
                                    index,
                                    'url',
                                    event.target
                                        .value,
                                )
                            }
                            placeholder="Tautan Video YouTube atau Vimeo (https://...)"
                            className="mt-4 w-full rounded-lg border border-input px-3 py-2"
                        />
                    )}

                {block.type
                    === 'cta' && (
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            <input
                                value={
                                    String(
                                        block
                                            .data
                                            .label
                                        ?? '',
                                    )
                                }
                                onChange={(event) =>
                                    patchData(
                                        index,
                                        'label',
                                        event.target
                                            .value,
                                    )
                                }
                                placeholder="Teks tombol aksi"
                                className="rounded-lg border border-input px-3 py-2"
                            />

                            <input
                                value={
                                    String(
                                        block
                                            .data
                                            .url
                                        ?? '',
                                    )
                                }
                                onChange={(event) =>
                                    patchData(
                                        index,
                                        'url',
                                        event.target
                                            .value,
                                    )
                                }
                                placeholder="Tautan tujuan (/kontak atau https://...)"
                                className="rounded-lg border border-input px-3 py-2"
                            />
                        </div>
                    )}
            </article>
            ),
                )}
        </div>
        </section >
    );
}
