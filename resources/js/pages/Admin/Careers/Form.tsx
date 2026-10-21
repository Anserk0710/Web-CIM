import { Head, router, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';
import { FormField } from '@/components/admin/FormField';
import { PageHeader } from '@/components/admin/PageHeader';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout';

type FormData = {
    expected_lock_version: number | null;
    slug: string;
    title: string;
    department: string;
    location: string;
    employment_type: string;
    summary: string;
    responsibilities: string;
    requirements: string;
    opens_at_local: string | null;
    closes_at_local: string | null;
    seo_title: string;
    seo_description: string;
};

type Props = {
    career: {
        id: number;
        status: string;
        lock_version: number;
    } | null;

    form: FormData;
    employmentTypes: string[];

    can?: {
        publish: boolean;
        close: boolean;
        archive: boolean;
    };
};

export default function Form({
    career,
    form: initial,
    employmentTypes,
    can,
}: Props) {
    const form = useForm<FormData>(
        initial,
    );

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        if (career === null) {
            form.post('/admin/careers');

            return;
        }

        form.put(`/admin/careers/${career.id}`);
    }

    return (
        <>
            <Head
                title={
                    career
                        ? 'Edit Career'
                        : 'Create Career'
                }
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Careers',
                        href: '/admin/careers',
                    },
                    {
                        label: career
                            ? 'Edit'
                            : 'Create',
                    },
                ]}
            >
                <PageHeader
                    title={
                        career
                            ? 'Edit Career'
                            : 'Create Career'
                    }
                    description={
                        career
                            ? `Status: ${career.status}`
                            : 'New Career starts as DRAFT.'
                    }
                    actions={
                        career && can ? (
                            <div className="flex flex-wrap gap-2">
                                {can.publish
                                    && career.status
                                    !== 'OPEN' && (
                                        <Button
                                            type="button"
                                            onClick={() => router.post(
                                                `/admin/careers/${career.id}/open`,
                                                {
                                                    lock_version:
                                                        career
                                                            .lock_version,
                                                },
                                            )}
                                        >
                                            Open
                                        </Button>
                                    )}

                                {can.close
                                    && career.status
                                    === 'OPEN' && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => router.post(
                                                `/admin/careers/${career.id}/close`,
                                                {
                                                    lock_version:
                                                        career
                                                            .lock_version,
                                                },
                                            )}
                                        >
                                            Close
                                        </Button>
                                    )}

                                {can.archive && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => router.post(
                                            `/admin/careers/${career.id}/archive`,
                                            {
                                                lock_version:
                                                    career
                                                        .lock_version,
                                            },
                                        )}
                                    >
                                        Archive
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
                            label="Title"
                            htmlFor="title"
                            error={form.errors.title}
                            required
                        >
                            <input
                                id="title"
                                value={form.data.title}
                                onChange={(event) => form.setData(
                                    'title',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Slug"
                            htmlFor="slug"
                            error={form.errors.slug}
                            required
                        >
                            <input
                                id="slug"
                                value={form.data.slug}
                                onChange={(event) => form.setData(
                                    'slug',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Department"
                            htmlFor="department"
                            required
                        >
                            <input
                                id="department"
                                value={
                                    form.data.department
                                }
                                onChange={(event) => form.setData(
                                    'department',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Location"
                            htmlFor="location"
                            required
                        >
                            <input
                                id="location"
                                value={
                                    form.data.location
                                }
                                onChange={(event) => form.setData(
                                    'location',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Employment Type"
                            htmlFor="employment_type"
                            required
                        >
                            <select
                                id="employment_type"
                                value={
                                    form.data
                                        .employment_type
                                }
                                onChange={(event) => form.setData(
                                    'employment_type',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            >
                                {employmentTypes.map(
                                    (type) => (
                                        <option
                                            key={type}
                                            value={type}
                                        >
                                            {type}
                                        </option>
                                    ),
                                )}
                            </select>
                        </FormField>

                        <div />

                        <FormField
                            label="Opens At (WITA)"
                            htmlFor="opens_at_local"
                        >
                            <input
                                id="opens_at_local"
                                type="datetime-local"
                                value={
                                    form.data
                                        .opens_at_local
                                    ?? ''
                                }
                                onChange={(event) => form.setData(
                                    'opens_at_local',
                                    event.target.value
                                    || null,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="Closes At (WITA)"
                            htmlFor="closes_at_local"
                            error={
                                form.errors
                                    .closes_at_local
                            }
                        >
                            <input
                                id="closes_at_local"
                                type="datetime-local"
                                value={
                                    form.data
                                        .closes_at_local
                                    ?? ''
                                }
                                onChange={(event) => form.setData(
                                    'closes_at_local',
                                    event.target.value
                                    || null,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </div>

                    {(
                        [
                            ['summary', 'Summary'],
                            ['responsibilities', 'Responsibilities'],
                            ['requirements', 'Requirements'],
                        ] as [string, string][]
                    ).map(([field, label]) => (
                        <FormField
                            key={field}
                            label={label}
                            htmlFor={field}
                        >
                            <textarea
                                id={field}
                                value={
                                    form.data[
                                        field as keyof FormData
                                    ] as string
                                }
                                onChange={(event) =>
                                    form.setData(
                                        field as
                                            | 'summary'
                                            | 'responsibilities'
                                            | 'requirements',
                                        event.target.value,
                                    )
                                }
                                className="min-h-32 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    ))}

                    <div className="grid gap-5 md:grid-cols-2">
                        <FormField
                            label="SEO Title"
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
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>

                        <FormField
                            label="SEO Description"
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
                                className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                            />
                        </FormField>
                    </div>

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            Save
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    )
}