import {
    Head,
    Link,
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

type Transition = {
    value: string;
    label: string;
    requires_reason: boolean;
};

type Application = {
    id: number;
    full_name: string | null;
    email: string | null;
    phone: string | null;
    introduction: string | null;
    portfolio_url: string | null;
    linkedin_url: string | null;
    status: string;
    cv_original_name: string | null;
    cv_size: number | null;
    has_cv: boolean;
    consented_at: string | null;
    selection_completed_at: string | null;
    retention_due_at: string | null;
    hr_handover_at: string | null;
    anonymized_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    lock_version: number;

    career: {
        id: number;
        title: string;
        slug: string;
        department: string;
        location: string;
    };

    consent_version: {
        version: string;
        purpose: string;
        effective_at: string | null;
    } | null;

    notes: Array<{
        id: number;
        body: string;
        created_at: string | null;

        author: {
            id: number;
            name: string;
            email: string;
        };
    }>;

    status_events: Array<{
        id: number;
        from_status: string | null;
        to_status: string;
        reason: string | null;
        created_at: string | null;

        actor: {
            id: number;
            name: string;
            email: string;
        } | null;
    }>;
};

type Props = {
    application: Application;
    transitions: Transition[];

    can: {
        update: boolean;
        download: boolean;
    };
};

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
        },
    ).format(
        new Date(value),
    );
}

function formatBytes(
    value: number | null,
): string {
    if (!value) {
        return '';
    }

    return `${(
        value
        / 1024
        / 1024
    ).toFixed(2)} MiB`;
}

export default function Show({
    application,
    transitions,
    can,
}: Props) {
    const statusForm = useForm({
        target_status:
            transitions[0]?.value
            ?? '',

        reason: '',

        lock_version:
            application.lock_version,
    });

    const noteForm = useForm({
        body: '',

        lock_version:
            application.lock_version,
    });

    const selectedTransition =
        transitions.find(
            (transition) =>
                transition.value
                === statusForm.data
                    .target_status,
        );

    function submitStatus(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        statusForm.transform((data) => ({
            ...data,
            lock_version: application.lock_version,
        }));

        statusForm.post(
            `/admin/applications/${application.id}/status`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    statusForm.reset('reason');
                },
            },
        );
    }

    function submitNote(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        noteForm.transform((data) => ({
            ...data,
            lock_version: application.lock_version,
        }));

        noteForm.post(
            `/admin/applications/${application.id}/notes`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    noteForm.reset();
                },
            },
        );
    }

    function recordHandover() {
        router.post(
            `/admin/applications/${application.id}/handover`,
            {
                lock_version: application.lock_version,
            },
            {
                preserveScroll: true,
            },
        );
    }

    return (
        <>
            <Head
                title={`Application #${application.id}`}
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Applications',
                        href: '/admin/applications',
                    },
                    {
                        label: `#${application.id}`,
                    },
                ]}
            >
                <PageHeader
                    title={
                        application.full_name
                        ?? `Application #${application.id}`
                    }
                    description={`${application.career.title}  ${application.status}`}
                    actions={
                        can.download
                        && application.has_cv
                            ? (
                                <a
                                    href={`/admin/applications/${application.id}/cv`}
                                    className="inline-flex h-10 items-center rounded-lg bg-cim-blue px-4 text-sm font-semibold text-white hover:bg-cim-navy"
                                >
                                    Download CV
                                </a>
                            )
                            : undefined
                    }
                />

                {application.anonymized_at && (
                    <div
                        role="status"
                        className="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950"
                    >
                        Application ini telah dianonimkan pada{' '}
                        {formatDate(
                            application
                                .anonymized_at,
                        )}.
                    </div>
                )}

                <div className="mt-8 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                    <div className="space-y-6">
                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Applicant
                            </h2>

                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Name
                                    </dt>

                                    <dd className="mt-1 break-words">
                                        {
                                            application
                                                .full_name
                                            ?? 'Anonymized'
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Email
                                    </dt>

                                    <dd className="mt-1 break-all">
                                        {
                                            application
                                                .email
                                            ?? 'Anonymized'
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Phone
                                    </dt>

                                    <dd className="mt-1">
                                        {
                                            application
                                                .phone
                                            ?? ''
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Submitted
                                    </dt>

                                    <dd className="mt-1">
                                        {formatDate(
                                            application
                                                .created_at,
                                        )}
                                    </dd>
                                </div>
                            </dl>

                            {application.introduction && (
                                <div className="mt-6">
                                    <h3 className="text-sm font-semibold text-cim-navy">
                                        Introduction
                                    </h3>

                                    <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                        {
                                            application
                                                .introduction
                                        }
                                    </p>
                                </div>
                            )}

                            <div className="mt-6 flex flex-wrap gap-3">
                                {application.portfolio_url && (
                                    <a
                                        href={
                                            application
                                                .portfolio_url
                                        }
                                        target="_blank"
                                        rel="noreferrer noopener"
                                        className="text-sm font-semibold text-cim-blue hover:text-cim-navy"
                                    >
                                        Portfolio
                                    </a>
                                )}

                                {application.linkedin_url && (
                                    <a
                                        href={
                                            application
                                                .linkedin_url
                                        }
                                        target="_blank"
                                        rel="noreferrer noopener"
                                        className="text-sm font-semibold text-cim-blue hover:text-cim-navy"
                                    >
                                        LinkedIn
                                    </a>
                                )}
                            </div>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Vacancy
                            </h2>

                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Position
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {
                                            application
                                                .career
                                                .title
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Department
                                    </dt>

                                    <dd className="mt-1">
                                        {
                                            application
                                                .career
                                                .department
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Location
                                    </dt>

                                    <dd className="mt-1">
                                        {
                                            application
                                                .career
                                                .location
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Public vacancy
                                    </dt>

                                    <dd className="mt-1">
                                        <a
                                            href={`/careers/${application.career.slug}`}
                                            target="_blank"
                                            rel="noreferrer noopener"
                                            className="font-semibold text-cim-blue hover:text-cim-navy"
                                        >
                                            Open page
                                        </a>
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Consent & CV
                            </h2>

                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Consent version
                                    </dt>

                                    <dd className="mt-1">
                                        {
                                            application
                                                .consent_version
                                                ?.version
                                            ?? ''
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Consented at
                                    </dt>

                                    <dd className="mt-1">
                                        {formatDate(
                                            application
                                                .consented_at,
                                        )}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        CV filename
                                    </dt>

                                    <dd className="mt-1 break-all">
                                        {
                                            application
                                                .cv_original_name
                                            ?? ''
                                        }
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        CV size
                                    </dt>

                                    <dd className="mt-1">
                                        {formatBytes(
                                            application
                                                .cv_size,
                                        )}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Notes
                            </h2>

                            {can.update
                                && !application.anonymized_at
                                && (
                                    <form
                                        onSubmit={
                                            submitNote
                                        }
                                        className="mt-5"
                                    >
                                        <FormField
                                            label="Add internal note"
                                            htmlFor="application_note"
                                            error={
                                                noteForm
                                                    .errors
                                                    .body
                                            }
                                        >
                                            <textarea
                                                id="application_note"
                                                value={
                                                    noteForm
                                                        .data
                                                        .body
                                                }
                                                onChange={(event) =>
                                                    noteForm
                                                        .setData(
                                                            'body',
                                                            event
                                                                .target
                                                                .value,
                                                        )
                                                }
                                                maxLength={5000}
                                                className="min-h-28 w-full rounded-lg border border-input px-3 py-2"
                                            />
                                        </FormField>

                                        {
                                            noteForm
                                                .errors
                                                .lock_version
                                            && (
                                                <p className="mt-2 text-sm text-destructive">
                                                    {
                                                        noteForm
                                                            .errors
                                                            .lock_version
                                                    }
                                                </p>
                                            )
                                        }

                                        <div className="mt-3 flex justify-end">
                                            <Button
                                                type="submit"
                                                disabled={
                                                    noteForm
                                                        .processing
                                                }
                                            >
                                                {
                                                    noteForm
                                                        .processing
                                                        ? 'Saving...'
                                                        : 'Add Note'
                                                }
                                            </Button>
                                        </div>
                                    </form>
                                )}

                            <div className="mt-6 space-y-4">
                                {application.notes.length === 0
                                    ? (
                                        <p className="text-sm text-muted-foreground">
                                            No notes yet.
                                        </p>
                                    )
                                    : application.notes.map(
                                        (note) => (
                                            <article
                                                key={note.id}
                                                className="rounded-xl border bg-muted/20 p-4"
                                            >
                                                <p className="whitespace-pre-wrap text-sm leading-6">
                                                    {
                                                        note.body
                                                    }
                                                </p>

                                                <p className="mt-3 text-xs text-muted-foreground">
                                                    {
                                                        note
                                                            .author
                                                            .name
                                                    }
                                                    {'  '}
                                                    {
                                                        formatDate(
                                                            note
                                                                .created_at,
                                                        )
                                                    }
                                                </p>
                                            </article>
                                        ),
                                    )}
                            </div>
                        </section>
                    </div>

                    <aside className="space-y-6">
                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Workflow
                            </h2>

                            <p className="mt-3 text-sm text-muted-foreground">
                                Current status
                            </p>

                            <p className="mt-1 text-xl font-bold text-cim-navy">
                                {application.status}
                            </p>

                            {can.update
                                && transitions.length > 0
                                && !application.anonymized_at
                                && (
                                    <form
                                        onSubmit={
                                            submitStatus
                                        }
                                        className="mt-6 space-y-4"
                                    >
                                        <FormField
                                            label="Next status"
                                            htmlFor="target_status"
                                            error={
                                                statusForm
                                                    .errors
                                                    .target_status
                                            }
                                        >
                                            <select
                                                id="target_status"
                                                value={
                                                    statusForm
                                                        .data
                                                        .target_status
                                                }
                                                onChange={(event) =>
                                                    statusForm
                                                        .setData(
                                                            'target_status',
                                                            event
                                                                .target
                                                                .value,
                                                        )
                                                }
                                                className="w-full rounded-lg border border-input px-3 py-2"
                                            >
                                                {transitions.map(
                                                    (
                                                        transition,
                                                    ) => (
                                                        <option
                                                            key={
                                                                transition
                                                                    .value
                                                            }
                                                            value={
                                                                transition
                                                                    .value
                                                            }
                                                        >
                                                            {
                                                                transition
                                                                    .label
                                                            }
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </FormField>

                                        <FormField
                                            label={
                                                selectedTransition
                                                    ?.requires_reason
                                                    ? 'Reason (required)'
                                                    : 'Reason (optional)'
                                            }
                                            htmlFor="status_reason"
                                            error={
                                                statusForm
                                                    .errors
                                                    .reason
                                            }
                                        >
                                            <textarea
                                                id="status_reason"
                                                value={
                                                    statusForm
                                                        .data
                                                        .reason
                                                }
                                                onChange={(event) =>
                                                    statusForm
                                                        .setData(
                                                            'reason',
                                                            event
                                                                .target
                                                                .value,
                                                        )
                                                }
                                                maxLength={2000}
                                                className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                                            />
                                        </FormField>

                                        {
                                            statusForm
                                                .errors
                                                .lock_version
                                            && (
                                                <p className="text-sm text-destructive">
                                                    {
                                                        statusForm
                                                            .errors
                                                            .lock_version
                                                    }
                                                </p>
                                            )
                                        }

                                        <Button
                                            type="submit"
                                            disabled={
                                                statusForm
                                                    .processing
                                            }
                                            className="w-full"
                                        >
                                            {
                                                statusForm
                                                    .processing
                                                    ? 'Updating...'
                                                    : 'Update Status'
                                            }
                                        </Button>
                                    </form>
                                )}

                            {transitions.length === 0 && (
                                <p className="mt-5 text-sm text-muted-foreground">
                                    No further transition is available.
                                </p>
                            )}
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Retention State
                            </h2>

                            <dl className="mt-4 space-y-4 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">
                                        Selection completed
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDate(
                                            application
                                                .selection_completed_at,
                                        )}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-muted-foreground">
                                        Retention due
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDate(
                                            application
                                                .retention_due_at,
                                        )}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-muted-foreground">
                                        HR handover
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDate(
                                            application
                                                .hr_handover_at,
                                        )}
                                    </dd>
                                </div>
                            </dl>

                            {can.update
                                && application.status === 'HIRED'
                                && !application.hr_handover_at
                                && !application.anonymized_at && (
                                    <div className="mt-4">
                                        <Button
                                            type="button"
                                            onClick={recordHandover}
                                            className="w-full"
                                        >
                                            Record HR handover
                                        </Button>
                                    </div>
                                )}
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Status History
                            </h2>

                            <ol className="mt-5 space-y-5 border-l pl-5">
                                {application.status_events.map(
                                    (event) => (
                                        <li
                                            key={event.id}
                                            className="relative"
                                        >
                                            <span
                                                aria-hidden="true"
                                                className="absolute -left-[1.55rem] top-1.5 size-2.5 rounded-full bg-cim-blue"
                                            />

                                            <p className="text-sm font-semibold text-cim-navy">
                                                {
                                                    event
                                                        .from_status
                                                    ?? ''
                                                }
                                                {'  '}
                                                {
                                                    event
                                                        .to_status
                                                }
                                            </p>

                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {
                                                    event
                                                        .actor
                                                        ?.name
                                                    ?? 'Public submission'
                                                }
                                                {'  '}
                                                {
                                                    formatDate(
                                                        event
                                                            .created_at,
                                                    )
                                                }
                                            </p>

                                            {event.reason && (
                                                <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-600">
                                                    {
                                                        event.reason
                                                    }
                                                </p>
                                            )}
                                        </li>
                                    ),
                                )}
                            </ol>
                        </section>
                    </aside>
                </div>

                <div className="mt-8">
                    <Link
                        href="/admin/applications"
                        className="text-sm font-semibold text-cim-blue hover:text-cim-navy"
                    >
                         Back to Applications
                    </Link>
                </div>
            </AdminLayout>
        </>
    );
}
