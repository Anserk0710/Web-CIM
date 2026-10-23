import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';
import { humanizeFieldKey, humanizeSectionKey } from '@/lib/humanize';

type Section = {
    key: string;
    is_enabled: boolean;
    data: Record<string, unknown>;
};

type Props = {
    page: {
        id: number;
        key: string;
        title: string;
        lock_version: number;
    };

    sections: Section[];
    canUpdate: boolean;
};

function defaultFields(key: string): Record<string, string> {
    switch (key) {
        case 'hero':
            return {
                eyebrow: '',
                title: '',
                description: '',
                image_id: '',
                image_alt: '',
                primary_cta_label: '',
                primary_cta_url: '',
                secondary_cta_label: '',
                secondary_cta_url: '',
            };

        case 'metrics':
            return {
                heading: '',
                items: '[]',
            };

        case 'capabilities':
            return {
                heading: '',
                intro: '',
                items: '[]',
            };

        case 'lifecycle':
            return {
                heading: '',
                items: '[]',
            };

        case 'featured_products':
            return {
                heading: '',
                product_ids: '[]',
            };

        case 'activity_preview':
            return {
                heading: '',
                intro: '',
                activity_ids: '[]',
            };

        case 'coverage':
            return {
                heading: '',
                intro: '',
                areas: '[]',
            };

        case 'cta':
            return {
                heading: '',
                description: '',
                primary_cta_label: '',
                primary_cta_url: '',
                secondary_cta_label: '',
                secondary_cta_url: '',
            };

        case 'story':
            return {
                heading: '',
                body: '',
                image_id: '',
                image_alt: '',
            };

        case 'vision_mission':
            return {
                heading: '',
                vision: '',
                mission: '',
            };

        case 'values':
            return {
                heading: '',
                items: '[]',
            };

        case 'milestones':
            return {
                heading: '',
                items: '[]',
            };

        case 'career_branding':
            return {
                heading: '',
                description: '',
                benefits: '[]',
                culture: '',
                growth: '',
            };

        case 'contact_intro':
            return {
                heading: '',
                description: '',
            };

        case 'policy':
            return {
                version: '',
                body: '',
            };

        default:
            return {};
    }
}

function stringifyStoredValue(value: unknown): string {
    if (typeof value === 'string') {
        return value;
    }

    if (value === null || value === undefined) {
        return '';
    }

    return JSON.stringify(value, null, 2);
}

function isJsonField(key: string): boolean {
    return [
        'items',
        'product_ids',
        'activity_ids',
        'areas',
        'benefits',
    ].includes(key);
}

export default function PageContent({
    page,
    sections,
    canUpdate,
}: Props) {
    const form = useForm({
        lock_version: page.lock_version,

        sections: sections.map((section) => {
            const storedData = Object.fromEntries(
                Object.entries(section.data).map(([key, value]) => [
                    key,
                    stringifyStoredValue(value),
                ]),
            );

            return {
                ...section,
                data: {
                    ...defaultFields(section.key),
                    ...storedData,
                },
            };
        }),
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.put(`/admin/settings/pages/${page.id}`);
    }

    return (
        <>
            <Head title={`Konten ${page.title}`} />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pengaturan',
                        href: '/admin/settings',
                    },
                    {
                        label: page.title,
                    },
                ]}
            >
                <PageHeader
                    title={`Konten Halaman: ${page.title}`}
                    description="Kelola teks, gambar pengantar, dan susunan bagian pada halaman situs web ini."
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    {form.data.sections.map((section, index) => (
                        <article
                            key={section.key}
                            className="rounded-xl border bg-background p-6 shadow-sm"
                        >
                            <div className="flex items-center justify-between gap-4 border-b pb-4">
                                <div>
                                    <h2 className="text-lg font-semibold text-cim-navy">
                                        {humanizeSectionKey(section.key)}
                                    </h2>
                                    <span className="font-mono text-xs text-muted-foreground">
                                        ID Bagian: {section.key}
                                    </span>
                                </div>

                                <label className="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={section.is_enabled}
                                        disabled={!canUpdate || form.processing}
                                        className="rounded border-input text-cim-blue focus:ring-cim-blue"
                                        onChange={(event) => {
                                            const next = [
                                                ...form.data.sections,
                                            ];

                                            next[index] = {
                                                ...section,
                                                is_enabled:
                                                    event.target.checked,
                                            };

                                            form.setData('sections', next);
                                        }}
                                    />
                                    <span>Tampilkan Bagian Ini di Situs</span>
                                </label>
                            </div>

                            <div className="mt-5 grid gap-4 md:grid-cols-2">
                                {Object.entries(section.data).map(
                                    ([key, value]) => (
                                        <label
                                            key={key}
                                            className={[
                                                'block',
                                                key === 'body' || key === 'description' || isJsonField(key)
                                                    ? 'md:col-span-2'
                                                    : '',
                                            ].join(' ')}
                                        >
                                            <span className="mb-2 block text-sm font-medium text-foreground">
                                                {humanizeFieldKey(key)}
                                            </span>

                                            <textarea
                                                value={value}
                                                maxLength={
                                                    key === 'version'
                                                        ? 40
                                                        : 5000
                                                }
                                                disabled={
                                                    !canUpdate ||
                                                    form.processing
                                                }
                                                onChange={(event) => {
                                                    const next = [
                                                        ...form.data.sections,
                                                    ];

                                                    next[index] = {
                                                        ...section,
                                                        data: {
                                                            ...section.data,
                                                            [key]: event.target.value,
                                                        },
                                                    };

                                                    form.setData(
                                                        'sections',
                                                        next,
                                                    );
                                                }}
                                                className="min-h-24 w-full rounded-lg border border-input px-3 py-2 text-sm font-sans"
                                            />

                                            {isJsonField(key) && (
                                                <span className="mt-1 block text-xs text-muted-foreground">
                                                    Format data JSON array, contoh: [{"{"}"judul":"Butir 1","keterangan":"Penjelasan"{"}"}]
                                                </span>
                                            )}

                                            {form.errors[
                                                `sections.${index}.data.${key}` as keyof typeof form.errors
                                            ] && (
                                                    <span className="mt-1 block text-xs text-destructive">
                                                        {
                                                            form.errors[
                                                            `sections.${index}.data.${key}` as keyof typeof form.errors
                                                            ]
                                                        }
                                                    </span>
                                                )}
                                        </label>
                                    ),
                                )}
                            </div>
                        </article>
                    ))}

                    {canUpdate && (
                        <div className="flex justify-end">
                            <Button
                                type="submit"
                                disabled={form.processing}
                            >
                                {form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Konten Halaman'}
                            </Button>
                        </div>
                    )}
                </form>
            </AdminLayout>
        </>
    );
}