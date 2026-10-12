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
            <Head title="Media" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Media',
                    },
                ]}
            >
                <PageHeader
                    title="Media Library"
                    description="Public media library terpisah dari private applicant/CV storage."
                />

                {flash.success && (
                    <div className="mt-5 rounded-xl bg-cim-ice p-4 text-sm text-cim-navy">
                        {flash.success}
                    </div>
                )}

                {can.upload && (
                    <form
                        onSubmit={submit}
                        className="mt-8 grid gap-4 rounded-xl border bg-background p-5 md:grid-cols-3"
                    >
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            onChange={(event) => form.setData(
                                'file',
                                event.target.files?.[0]
                                ?? null,
                            )}
                            required
                        />

                        <input
                            placeholder="Default alt"
                            value={
                                form.data.default_alt
                            }
                            onChange={(event) => form.setData(
                                'default_alt',
                                event.target.value,
                            )}
                            className="rounded-lg border border-input px-3 py-2"
                        />

                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            Upload
                        </Button>
                    </form>
                )}

                <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {media.data.map((item) => (
                        <article
                            key={item.id}
                            className="rounded-xl border bg-background p-4"
                        >
                            {item.mime.startsWith(
                                'image/',
                            ) ? (
                                <img
                                    src={item.url}
                                    alt=""
                                    className="aspect-video w-full rounded-lg object-cover"
                                />
                            ) : (
                                <div className="flex aspect-video items-center justify-center rounded-lg bg-muted font-semibold">
                                    PDF
                                </div>
                            )}

                            <p className="mt-3 truncate text-sm font-medium">
                                {item.name}
                            </p>

                            <p className="mt-1 text-xs text-muted-foreground">
                                {item.mime}
                            </p>

                            {can.delete && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="mt-4"
                                    onClick={() => {
                                        if (
                                            window.confirm(
                                                'Hapus media ini?',
                                            )
                                        ) {
                                            router.delete(
                                                `/admin/media/${item.id}`,
                                            );
                                        }
                                    }}
                                >
                                    Delete
                                </Button>
                            )}
                        </article>
                    ))}
                </div>
            </AdminLayout>
        </>
    );
}