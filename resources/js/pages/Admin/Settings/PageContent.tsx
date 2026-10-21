import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

type Section = {
    key: string;
    is_enabled: boolean;
    data: Record<string, string>;
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

function defaultFields(
    key: string,
): Record<string, string> {
    switch (key) {
        case 'hero':
            return {
                eyebrow: '',
                title: '',
                description: '',
                cta_label: '',
                cta_url: '',
            };

        case 'capabilities':
            return {
                heading: '',
                intro: '',
                items: '',
            };

        case 'lifecycle':
            return {
                heading: '',
                items: '',
            };

        case 'values':
            return {
                heading: '',
                items: '',
            };

        case 'milestones':
            return {
                heading: '',
                items: '',
            };

        case 'career_branding':
            return {
                heading: '',
                description: '',
                benefits: '',
            };

        default:
            return {};
    }
}

export default function PageContent({
    page,
    sections,
    canUpdate,
}: Props) {
    const form = useForm({
        lock_version:
            page.lock_version,

        sections:
            sections.map(
                (section) => ({
                    ...section,

                    data: {
                        ...defaultFields(
                            section.key,
                        ),

                        ...section.data,
                    },
                }),
            ),
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.put(
            `/admin/settings/pages/${page.id}`,
        );
    }

    return (
        <>
            <Head
                title={`${page.key} Content`}
            />

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
                    description="Structured content only."
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    {form.data.sections.map(
                        (section, index) => (
                            <article
                                key={section.key}
                                className="rounded-xl border bg-background p-6"
                            >
                                <div className="flex items-center justify-between gap-4">
                                    <h2 className="font-semibold text-cim-navy">
                                        {
                                            section.key
                                        }
                                    </h2>

                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={
                                                section
                                                    .is_enabled
                                            }
                                            onChange={(event) => {
                                                const next = [
                                                    ...form
                                                        .data
                                                        .sections,
                                                ];

                                                next[index] = {
                                                    ...section,

                                                    is_enabled:
                                                        event
                                                            .target
                                                            .checked,
                                                };

                                                form.setData(
                                                    'sections',
                                                    next,
                                                );
                                            }}
                                        />

                                        Enabled
                                    </label>
                                </div>

                                <div className="mt-5 grid gap-4 md:grid-cols-2">
                                    {Object.entries(
                                        section.data,
                                    ).map(
                                        ([
                                            key,
                                            value,
                                        ]) => (
                                            <label
                                                key={
                                                    key
                                                }
                                                className="block"
                                            >
                                                <span className="mb-2 block text-sm font-medium">
                                                    {
                                                        key
                                                    }
                                                </span>

                                                <textarea
                                                    value={
                                                        String(
                                                            value
                                                            ?? '',
                                                        )
                                                    }
                                                    onChange={(event) => {
                                                        const next = [
                                                            ...form
                                                                .data
                                                                .sections,
                                                        ];

                                                        next[
                                                            index
                                                        ] = {
                                                            ...section,

                                                            data: {
                                                                ...section.data,

                                                                [key]:
                                                                    event
                                                                        .target
                                                                        .value,
                                                            },
                                                        };

                                                        form.setData(
                                                            'sections',
                                                            next,
                                                        );
                                                    }}
                                                    className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                                                />
                                            </label>
                                        ),
                                    )}
                                </div>
                            </article>
                        ),
                    )}

                    {canUpdate && (
                        <div className="flex justify-end">
                            <Button type="submit">
                                Save Page Content
                            </Button>
                        </div>
                    )}
                </form>
            </AdminLayout>
        </>
    );
}
