import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import {
    PageHeader,
} from '@/components/admin/PageHeader';

import AdminLayout from '@/layouts/AdminLayout';

import {
    type SharedPageProps,
} from '@/types';

type Media = {
    id: number;
    name: string;
    mime: string;
    size_bytes: number;
    url: string;
};

type Props = {
    media: {
        data: Media[];
    };

    can: {
        upload: boolean;
        delete: boolean;
    };
};

function formatFileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function Index({
    media,
    can,
}: Props) {
    const {
        flash,
    } = usePage<SharedPageProps>().props;

    const form = useForm<{
        file: File | null;
        default_alt: string;
        caption: string;
    }>({
        file: null,
        default_alt: '',
        caption: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post('/admin/media', {
            forceFormData: true,

            onSuccess: () => form.reset(),
        });
    }

    return (
        <>
            <Head title="Pustaka Media & Berkas" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pustaka Media',
                    },
                ]}
            >
                <PageHeader
                    title="Pustaka Media & Berkas"
                    description="Kelola gambar dan dokumen publik untuk materi produk, kegiatan, dan konten situs web."
                />

                {flash.success && (
                    <div className="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                        {flash.success}
                    </div>
                )}

                {can.upload && (
                    <form
                        onSubmit={submit}
                        className="mt-8 grid gap-4 rounded-xl border bg-background p-5 md:grid-cols-[1.5fr_1.5fr_auto] items-end"
                    >
                        <div>
                            <label className="block text-xs font-medium text-muted-foreground mb-1">
                                Pilih Berkas (Gambar JPG, PNG, WebP atau Dokumen PDF)
                            </label>
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp,application/pdf"
                                onChange={(event) => form.setData(
                                    'file',
                                    event.target.files?.[0]
                                    ?? null,
                                )}
                                className="w-full text-xs"
                                required
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-medium text-muted-foreground mb-1">
                                Keterangan Gambar (Alt Text)
                            </label>
                            <input
                                placeholder="Deskripsi singkat gambar untuk aksesibilitas"
                                value={form.data.default_alt}
                                onChange={(event) => form.setData(
                                    'default_alt',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                            />
                        </div>

                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            {form.processing ? 'Mengunggah...' : 'Unggah Berkas'}
                        </Button>
                    </form>
                )}

                <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {media.data.map((item) => (
                        <article
                            key={item.id}
                            className="rounded-xl border bg-background p-4 shadow-sm"
                        >
                            {item.mime.startsWith(
                                'image/',
                            ) ? (
                                <img
                                    src={item.url}
                                    alt={item.name}
                                    className="aspect-video w-full rounded-lg object-cover bg-muted"
                                />
                            ) : (
                                <div className="flex aspect-video items-center justify-center rounded-lg bg-red-50 text-red-700 font-semibold text-lg border border-red-200">
                                    Dokumen PDF
                                </div>
                            )}

                            <p className="mt-3 truncate text-sm font-medium text-cim-navy" title={item.name}>
                                {item.name}
                            </p>

                            <p className="mt-1 text-xs text-muted-foreground">
                                {item.mime} · {formatFileSize(item.size_bytes)}
                            </p>

                            {can.delete && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="mt-4 text-xs text-red-600 hover:text-red-700 hover:bg-red-50"
                                    onClick={() => {
                                        if (
                                            window.confirm(
                                                'Apakah Anda yakin ingin menghapus berkas media ini?',
                                            )
                                        ) {
                                            router.delete(
                                                `/admin/media/${item.id}`,
                                            );
                                        }
                                    }}
                                >
                                    Hapus Berkas
                                </Button>
                            )}
                        </article>
                    ))}

                    {media.data.length === 0 && (
                        <div className="col-span-full rounded-xl border border-dashed p-12 text-center text-muted-foreground">
                            Belum ada berkas media yang diunggah ke pustaka.
                        </div>
                    )}
                </div>
            </AdminLayout>
        </>
    );
}