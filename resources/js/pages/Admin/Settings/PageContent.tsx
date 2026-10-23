import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

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
            <Head title={`${page.key} Content`} />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Settings',
                        href: '/admin/settings',
                    },
                    {
                        label: page.key,
                    },
                ]}
            >
                <PageHeader
                    title={`${page.title} Content`}
                    description="Structured content only. Privacy version must be changed whenever the published notice changes."
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    {form.data.sections.map((section, index) => (
                        <article
                            key={section.key}
                            className="rounded-xl border bg-background p-6"
                        >
                            <div className="flex items-center justify-between gap-4">
                                <h2 className="font-semibold text-cim-navy">
                                    {section.key}
                                </h2>

                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={section.is_enabled}
                                        disabled={!canUpdate || form.processing}
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

                                    Enabled
                                </label>
                            </div>

                            <div className="mt-5 grid gap-4 md:grid-cols-2">
                                {Object.entries(section.data).map(
                                    ([key, value]) => (
                                        <label
                                            key={key}
                                            className="block"
                                        >
                                            <span className="mb-2 block text-sm font-medium">
                                                {key.replaceAll('_', ' ')}
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
                                                className={[
                                                    'min-h-24 w-full rounded-lg border border-input px-3 py-2',
                                                    key === 'body'
                                                        ? 'md:col-span-2'
                                                        : '',
                                                ].join(' ')}
                                            />

                                            {isJsonField(key) && (
                                                <span className="mt-1 block text-xs text-muted-foreground">
                                                    Masukkan JSON array yang valid. Contoh: [{"{"}"title":"Contoh","body":"Deskripsi"{"}"}]
                                                </span>
                                            )}

                                            {form.errors[
                                                `sections.${index}.data.${key}` as keyof typeof form.errors
                                            ] && (
                                                    <span className="mt-1 block text-sm text-destructive">
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
                                    ? 'Saving...'
                                    : 'Save Page Content'}
                            </Button>
                        </div>
                    )}
                </form>
            </AdminLayout>
        </>
    );
}