// path: resources/js/pages/Admin/Inquiries/Show.tsx

import {
    Head,
    Link,
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

type Note = {
    id: number;
    body: string;
    created_at: string | null;
    author: {
        id: number;
        name: string;
    };
};

type HistoryItem = {
    id: number;
    from_status: string | null;
    to_status: string | null;
    reason_note_id: number | null;
    created_at: string | null;
    actor: {
        id: number;
        name: string;
    } | null;
};

type Inquiry = {
    id: number;
    type: string;
    status: string;
    name: string | null;
    email: string | null;
    phone: string | null;
    organization: string | null;
    message: string | null;
    privacy_notice_version: string;
    resolved_at: string | null;
    spam_marked_at: string | null;
    anonymized_at: string | null;
    created_at: string | null;
    lock_version: number;

    product: {
        id: number | null;
        name: string | null;
    };

    ecatalog: {
        id: number | null;
        catalog_code: string | null;
        title: string | null;
    };

    notes: Note[];
};

type Props = {
    inquiry: Inquiry;
    transitions: Transition[];
    history: HistoryItem[];

    can: {
        update: boolean;
    };
};

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
        },
    ).format(new Date(value));
}

export default function Show({
    inquiry,
    transitions,
    history,
    can,
}: Props) {
    const statusForm = useForm({
        target_status:
            transitions[0]?.value ?? '',

        reason: '',

        lock_version:
            inquiry.lock_version,
    });

    const noteForm = useForm({
        body: '',

        lock_version:
            inquiry.lock_version,
    });

    const selectedTransition = transitions.find(
        (transition) =>
            transition.value
            === statusForm.data.target_status,
    );

    function submitStatus(
        event: FormEvent<HTMLFormElement>,
    ): void {
        event.preventDefault();

        statusForm.transform((data) => ({
            ...data,

            lock_version:
                inquiry.lock_version,
        }));

        statusForm.post(
            `/admin/inquiries/${inquiry.id}/status`,
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
    ): void {
        event.preventDefault();

        noteForm.transform((data) => ({
            ...data,

            lock_version:
                inquiry.lock_version,
        }));

        noteForm.post(
            `/admin/inquiries/${inquiry.id}/notes`,
            {
                preserveScroll: true,

                onSuccess: () => {
                    noteForm.reset();
                },
            },
        );
    }

    return (
        <>
            <Head
                title={`Inquiry #${inquiry.id}`}
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Inquiries',
                        href: '/admin/inquiries',
                    },
                    {
                        label: `#${inquiry.id}`,
                    },
                ]}
            >
                <PageHeader
                    title={
                        inquiry.name
                        ?? `Inquiry #${inquiry.id}`
                    }
                    description={`${inquiry.type} · ${inquiry.status}`}
                />

                {inquiry.anonymized_at && (
                    <div
                        role="status"
                        className="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950"
                    >
                        Inquiry telah dianonimkan pada{' '}
                        {formatDate(
                            inquiry.anonymized_at,
                        )}.
                        Identitas, message, dan notes tidak ditampilkan.
                    </div>
                )}

                <div className="mt-8 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                    <div className="space-y-6">
                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Contact details
                            </h2>

                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Name
                                    </dt>

                                    <dd className="mt-1 break-words">
                                        {inquiry.name ?? 'Anonymized'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Email
                                    </dt>

                                    <dd className="mt-1 break-all">
                                        {inquiry.email ?? 'Anonymized'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Phone
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.phone ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Organization
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.organization ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Received
                                    </dt>

                                    <dd className="mt-1">
                                        {formatDate(inquiry.created_at)}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Privacy version
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.privacy_notice_version}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Message
                            </h2>

                            <p className="mt-4 whitespace-pre-wrap break-words text-sm leading-7 text-slate-700">
                                {inquiry.message ?? 'No message available.'}
                            </p>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Related content
                            </h2>

                            <dl className="mt-5 space-y-5">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Product
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.product.name ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        E-Catalog
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.ecatalog.catalog_code
                                            ? `${inquiry.ecatalog.catalog_code} — ${inquiry.ecatalog.title ?? ''}`
                                            : '—'}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Internal notes
                            </h2>

                            {can.update
                                && !inquiry.anonymized_at
                                && (
                                    <form
                                        onSubmit={submitNote}
                                        className="mt-5"
                                    >
                                        <FormField
                                            label="Add internal note"
                                            htmlFor="inquiry_note"
                                            error={noteForm.errors.body}
                                        >
                                            <textarea
                                                id="inquiry_note"
                                                value={noteForm.data.body}
                                                onChange={(event) =>
                                                    noteForm.setData(
                                                        'body',
                                                        event.target.value,
                                                    )
                                                }
                                                maxLength={5000}
                                                className="min-h-28 w-full rounded-lg border border-input px-3 py-2"
                                            />
                                        </FormField>

                                        {noteForm.errors.lock_version && (
                                            <p className="mt-2 text-sm text-destructive">
                                                {noteForm.errors.lock_version}
                                            </p>
                                        )}

                                        <div className="mt-3 flex justify-end">
                                            <Button
                                                type="submit"
                                                disabled={noteForm.processing}
                                            >
                                                {noteForm.processing
                                                    ? 'Saving...'
                                                    : 'Add note'}
                                            </Button>
                                        </div>
                                    </form>
                                )}

                            <div className="mt-6 space-y-4">
                                {inquiry.notes.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No notes available.
                                    </p>
                                ) : (
                                    inquiry.notes.map((note) => (
                                        <article
                                            key={note.id}
                                            className="rounded-xl border bg-muted/20 p-4"
                                        >
                                            <p className="whitespace-pre-wrap break-words text-sm leading-6">
                                                {note.body}
                                            </p>

                                            <p className="mt-3 text-xs text-muted-foreground">
                                                {note.author.name}
                                                {' · '}
                                                {formatDate(note.created_at)}
                                            </p>
                                        </article>
                                    ))
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
                                {inquiry.status}
                            </p>

                            {can.update
                                && transitions.length > 0
                                && !inquiry.anonymized_at
                                && (
                                    <form
                                        onSubmit={submitStatus}
                                        className="mt-6 space-y-4"
                                    >
                                        <FormField
                                            label="Next status"
                                            htmlFor="target_status"
                                            error={statusForm.errors.target_status}
                                        >
                                            <select
                                                id="target_status"
                                                value={statusForm.data.target_status}
                                                onChange={(event) =>
                                                    statusForm.setData(
                                                        'target_status',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-lg border border-input px-3 py-2"
                                            >
                                                {transitions.map(
                                                    (transition) => (
                                                        <option
                                                            key={transition.value}
                                                            value={transition.value}
                                                        >
                                                            {transition.label}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </FormField>

                                        <FormField
                                            label={
                                                selectedTransition?.requires_reason
                                                    ? 'Reopen reason (required)'
                                                    : 'Reason (optional)'
                                            }
                                            htmlFor="status_reason"
                                            error={statusForm.errors.reason}
                                        >
                                            <textarea
                                                id="status_reason"
                                                value={statusForm.data.reason}
                                                onChange={(event) =>
                                                    statusForm.setData(
                                                        'reason',
                                                        event.target.value,
                                                    )
                                                }
                                                maxLength={2000}
                                                className="min-h-24 w-full rounded-lg border border-input px-3 py-2"
                                            />
                                        </FormField>

                                        {statusForm.errors.lock_version && (
                                            <p className="text-sm text-destructive">
                                                {statusForm.errors.lock_version}
                                            </p>
                                        )}

                                        <Button
                                            type="submit"
                                            disabled={statusForm.processing}
                                            className="w-full"
                                        >
                                            {statusForm.processing
                                                ? 'Updating...'
                                                : 'Update status'}
                                        </Button>
                                    </form>
                                )}
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Status history
                            </h2>

                            <ol className="mt-5 space-y-5 border-l pl-5">
                                {history.length === 0 ? (
                                    <li className="text-sm text-muted-foreground">
                                        No status changes recorded.
                                    </li>
                                ) : (
                                    history.map((event) => (
                                        <li
                                            key={event.id}
                                            className="relative"
                                        >
                                            <span
                                                aria-hidden="true"
                                                className="absolute -left-[1.55rem] top-1.5 size-2.5 rounded-full bg-cim-blue"
                                            />

                                            <p className="text-sm font-semibold text-cim-navy">
                                                {event.from_status ?? '—'}
                                                {' → '}
                                                {event.to_status ?? '—'}
                                            </p>

                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {event.actor?.name ?? 'System'}
                                                {' · '}
                                                {formatDate(event.created_at)}
                                            </p>

                                            {event.reason_note_id && (
                                                <p className="mt-2 text-xs text-muted-foreground">
                                                    Reason recorded in note #{event.reason_note_id}.
                                                </p>
                                            )}
                                        </li>
                                    ))
                                )}
                            </ol>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Resolution
                            </h2>

                            <dl className="mt-4 space-y-4 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">
                                        Resolved at
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDate(inquiry.resolved_at)}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-muted-foreground">
                                        Marked as spam
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDate(inquiry.spam_marked_at)}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>

                <div className="mt-8">
                    <Link
                        href="/admin/inquiries"
                        className="text-sm font-semibold text-cim-blue hover:text-cim-navy"
                    >
                        ← Back to Inquiries
                    </Link>
                </div>
            </AdminLayout>
        </>
    );
}
