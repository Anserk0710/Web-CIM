import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { FormField } from '@/components/admin/FormField';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

type MediaOption = {
    id: number;
    name: string;
};

type Page = {
    id: number;
    key: string;
    title: string;
};

type Office = {
    id: number;
    name: string;
    address: string;
    is_head_office: boolean;
    is_active: boolean;
};

type Settings = {
    company: {
        legal_name: string;
        display_name: string;
        about: string;
    };

    contact: {
        email: string;
        phone: string;
        whatsapp: string;
    };

    coverage: {
        regions: string[];
    };

    seo_defaults: {
        title_suffix: string;
        default_description: string;
    };

    branding: {
        logo_media_id: number | null;
        favicon_media_id: number | null;
    };

    footer: {
        tagline: string;
        copyright: string;
    };
};

type Props = {
    settings: Settings;
    offices: Office[];
    pages: Page[];
    media: MediaOption[];
    canUpdate: boolean;
};

export default function Index({
    settings,
    offices,
    pages,
    media,
    canUpdate,
}: Props) {
    const form = useForm<Settings>(
        settings,
    );

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.put('/admin/settings');
    }

    return (
        <>
            <Head title="Settings" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Settings',
                    },
                ]}
            >
                <PageHeader
                    title="Site Settings"
                    description="Structured settings only. No arbitrary HTML, CSS, or scripts."
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    <section className="rounded-xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            Company
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField
                                label="Legal Name"
                                htmlFor="legal_name"
                            >
                                <input
                                    id="legal_name"
                                    value={
                                        form.data
                                            .company
                                            .legal_name
                                    }
                                    onChange={(event) => form.setData(
                                        'company',
                                        {
                                            ...form.data.company,
                                            legal_name:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField
                                label="Display Name"
                                htmlFor="display_name"
                            >
                                <input
                                    id="display_name"
                                    value={
                                        form.data
                                            .company
                                            .display_name
                                    }
                                    onChange={(event) => form.setData(
                                        'company',
                                        {
                                            ...form.data.company,
                                            display_name:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>
                        </div>

                        <FormField
                            label="About"
                            htmlFor="about"
                        >
                            <textarea
                                id="about"
                                value={
                                    form.data
                                        .company
                                        .about
                                }
                                onChange={(event) => form.setData(
                                    'company',
                                    {
                                        ...form.data.company,
                                        about:
                                            event
                                                .target
                                                .value,
                                    },
                                )}
                                className="mt-5 min-h-32 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </section>

                    <section className="rounded-xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            Contact & Coverage
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-3">
                            {(
                                [
                                    'email',
                                    'phone',
                                    'whatsapp',
                                ] as (keyof Settings['contact'])[]
                            ).map((field) => (
                                <FormField
                                    key={field}
                                    label={field}
                                >
                                    <input
                                        value={
                                            form.data
                                                .contact[
                                                field
                                            ]
                                        }
                                        onChange={(event) =>
                                            form.setData(
                                                'contact',
                                                {
                                                    ...form.data.contact,
                                                    [field]:
                                                        event
                                                            .target
                                                            .value,
                                                },
                                            )
                                        }
                                        className="w-full rounded-lg border border-input px-3 py-2"
                                    />
                                </FormField>
                            ))}
                        </div>

                        <FormField
                            label="Coverage Regions"
                            hint="One region per line."
                        >
                            <textarea
                                value={
                                    form.data
                                        .coverage
                                        .regions
                                        .join('\n')
                                }
                                onChange={(event) => form.setData(
                                    'coverage',
                                    {
                                        regions:
                                            event
                                                .target
                                                .value
                                                .split(
                                                    '\n',
                                                )
                                                .map(
                                                    (
                                                        value,
                                                    ) =>
                                                        value.trim(),
                                                )
                                                .filter(
                                                    Boolean,
                                                ),
                                    },
                                )}
                                className="mt-5 min-h-28 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </section>

                    <section className="rounded-xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            Branding & SEO
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField label="Logo">
                                <select
                                    value={
                                        form.data
                                            .branding
                                            .logo_media_id
                                        ?? ''
                                    }
                                    onChange={(event) => form.setData(
                                        'branding',
                                        {
                                            ...form.data.branding,

                                            logo_media_id:
                                                event
                                                    .target
                                                    .value
                                                    ? Number(
                                                        event
                                                            .target
                                                            .value,
                                                    )
                                                    : null,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                >
                                    <option value="">
                                        None
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
                            </FormField>

                            <FormField label="Favicon">
                                <select
                                    value={
                                        form.data
                                            .branding
                                            .favicon_media_id
                                        ?? ''
                                    }
                                    onChange={(event) => form.setData(
                                        'branding',
                                        {
                                            ...form.data.branding,

                                            favicon_media_id:
                                                event
                                                    .target
                                                    .value
                                                    ? Number(
                                                        event
                                                            .target
                                                            .value,
                                                    )
                                                    : null,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                >
                                    <option value="">
                                        None
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
                            </FormField>

                            <FormField label="Title Suffix">
                                <input
                                    value={
                                        form.data
                                            .seo_defaults
                                            .title_suffix
                                    }
                                    onChange={(event) => form.setData(
                                        'seo_defaults',
                                        {
                                            ...form.data
                                                .seo_defaults,

                                            title_suffix:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField label="Default Description">
                                <textarea
                                    value={
                                        form.data
                                            .seo_defaults
                                            .default_description
                                    }
                                    onChange={(event) => form.setData(
                                        'seo_defaults',
                                        {
                                            ...form.data
                                                .seo_defaults,

                                            default_description:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>
                        </div>
                    </section>

                    <section className="rounded-xl border bg-background p-6">
                        <h2 className="font-semibold text-cim-navy">
                            Footer
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField label="Tagline">
                                <input
                                    value={
                                        form.data
                                            .footer
                                            .tagline
                                    }
                                    onChange={(event) => form.setData(
                                        'footer',
                                        {
                                            ...form.data.footer,

                                            tagline:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>

                            <FormField label="Copyright">
                                <input
                                    value={
                                        form.data
                                            .footer
                                            .copyright
                                    }
                                    onChange={(event) => form.setData(
                                        'footer',
                                        {
                                            ...form.data.footer,

                                            copyright:
                                                event
                                                    .target
                                                    .value,
                                        },
                                    )}
                                    className="w-full rounded-lg border border-input px-3 py-2"
                                />
                            </FormField>
                        </div>
                    </section>

                    {canUpdate && (
                        <div className="flex justify-end">
                            <Button
                                type="submit"
                                disabled={
                                    form.processing
                                }
                            >
                                Save Settings
                            </Button>
                        </div>
                    )}
                </form>

                <section className="mt-10">
                    <h2 className="text-xl font-bold text-cim-navy">
                        Structured Pages
                    </h2>

                    <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {pages.map((page) => (
                            <Link
                                key={page.id}
                                href={`/admin/settings/pages/${page.id}`}
                                className="rounded-xl border bg-background p-4 font-medium hover:border-cim-blue"
                            >
                                {page.key}
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="mt-10">
                    <h2 className="text-xl font-bold text-cim-navy">
                        Offices
                    </h2>

                    <div className="mt-4 space-y-3">
                        {offices.map(
                            (office) => (
                                <div
                                    key={office.id}
                                    className="rounded-xl border bg-background p-4"
                                >
                                    <p className="font-semibold">
                                        {
                                            office.name
                                        }
                                    </p>

                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {
                                            office.address
                                        }
                                    </p>

                                    <p className="mt-2 text-xs">
                                        {office.is_head_office
                                            ? 'HEAD OFFICE · '
                                            : ''}
                                        {office.is_active
                                            ? 'ACTIVE'
                                            : 'INACTIVE'}
                                    </p>
                                </div>
                            ),
                        )}
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}