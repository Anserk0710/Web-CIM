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
import {
    humanizeAvailability,
    humanizeStatus,
} from '@/lib/humanize';

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
                        ? 'Ubah E-Katalog'
                        : 'Tambah E-Katalog Baru'
                }
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'E-Katalog',
                        href: '/admin/ecatalog',
                    },
                    {
                        label: entry
                            ? 'Ubah'
                            : 'Tambah Baru',
                    },
                ]}
            >
                <PageHeader
                    title={
                        entry
                            ? 'Ubah E-Katalog'
                            : 'Tambah E-Katalog Baru'
                    }
                    description={
                        entry
                            ? `Status Saat Ini: ${humanizeStatus(entry.status)}`
                            : 'Data baru akan tersimpan terlebih dahulu sebagai Draf.'
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
                                        Terbitkan
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
                                        Arsipkan
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
                                                        'Apakah Anda yakin ingin menghapus draf E-Katalog ini?',
                                                    )
                                                ) {
                                                    router.delete(
                                                        `/admin/ecatalog/${entry.id}`,
                                                    );
                                                }
                                            }}
                                        >
                                            Hapus Draf
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
                            label="Produk Alat Kesehatan Terkait"
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
                                    Pilih Produk Terkait
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
                            label="Kode E-Katalog LKPP"
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
                                placeholder="Contoh: E-CAT-2026-001"
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Nama Pengadaan / Paket"
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
                                placeholder="Nama paket pengadaan sesuai LKPP"
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Kategori Pengadaan"
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
                                placeholder="Contoh: Alat Kesehatan Radiologi"
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Ketersediaan Stok"
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
                                            {humanizeAvailability(value)}
                                        </option>
                                    ),
                                )}
                            </select>
                        </FormField>

                        <FormField
                            label="Satuan / Kemasan Produk"
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
                                placeholder="Contoh: Unit / Set / Box"
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Tanggal Mulai Berlaku"
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
                            label="Tanggal Berakhir Masa Berlaku"
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
                            label="Tautan Resmi E-Katalog (LKPP)"
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
                                placeholder="https://e-katalog.lkpp.go.id/..."
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Urutan Tampilan"
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
                        label="Catatan / Keterangan Spesifikasi"
                        htmlFor="notes"
                    >
                        <textarea
                            id="notes"
                            value={form.data.notes}
                            onChange={(event) => form.setData(
                                'notes',
                                event.target.value,
                            )}
                            placeholder="Tambahkan informasi spesifikasi, nomor izin edar AKL/AKD, atau catatan pengadaan..."
                            className="min-h-28 w-full rounded-lg border border-input px-3 py-2"
                        />
                    </FormField>

                    <div className="grid gap-5 md:grid-cols-2">
                        <FormField
                            label="Judul Penelusuran (SEO Title)"
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
                                placeholder="Judul ringkas untuk hasil mesin pencari"
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

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
                                placeholder="Ringkasan informatif 150-160 karakter untuk hasil pencarian"
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
                                ? 'Menyimpan...'
                                : 'Simpan Data'}
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}