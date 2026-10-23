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
import {
    formatDateTimeIndonesian,
    humanizeInquiryType,
    humanizeStatus,
} from '@/lib/humanize';

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
                title={`Pesan Masuk #${inquiry.id}`}
            />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pesan Masuk',
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
                        ?? `Pesan Masuk #${inquiry.id}`
                    }
                    description={`${humanizeInquiryType(inquiry.type)} · ${humanizeStatus(inquiry.status)}`}
                />

                {inquiry.anonymized_at && (
                    <div
                        role="status"
                        className="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950"
                    >
                        Pesan ini telah disamarkan pada{' '}
                        {formatDateTimeIndonesian(
                            inquiry.anonymized_at,
                        )}{' '}
                        demi perlindungan data pribadi. Identitas pengirim, isi pesan, dan catatan internal tidak lagi ditampilkan.
                    </div>
                )}

                <div className="mt-8 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                    <div className="space-y-6">
                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Informasi Kontak Pengirim
                            </h2>

                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Nama Pengirim
                                    </dt>

                                    <dd className="mt-1 break-words">
                                        {inquiry.name ?? 'Data telah disamarkan'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Alamat Email
                                    </dt>

                                    <dd className="mt-1 break-all">
                                        {inquiry.email ?? 'Data telah disamarkan'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Nomor Telepon / WhatsApp
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.phone ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Instansi / Perusahaan
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.organization ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Waktu Diterima
                                    </dt>

                                    <dd className="mt-1">
                                        {formatDateTimeIndonesian(inquiry.created_at)}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Versi Kebijakan Privasi
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.privacy_notice_version}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Isi Pesan
                            </h2>

                            <p className="mt-4 whitespace-pre-wrap break-words text-sm leading-7 text-slate-700">
                                {inquiry.message ?? 'Tidak ada isi pesan yang disertakan.'}
                            </p>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Konten Terkait
                            </h2>

                            <dl className="mt-5 space-y-5">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Produk Terkait
                                    </dt>

                                    <dd className="mt-1">
                                        {inquiry.product.name ?? '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Katalog Digital Terkait
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
                                Catatan Tim Internal
                            </h2>

                            {can.update
                                && !inquiry.anonymized_at
                                && (
                                    <form
                                        onSubmit={submitNote}
                                        className="mt-5"
                                    >
                                        <FormField
                                            label="Tulis catatan internal"
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
                                                placeholder="Tambahkan catatan tindak lanjut pesan..."
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
                                                    ? 'Menyimpan...'
                                                    : 'Simpan Catatan'}
                                            </Button>
                                        </div>
                                    </form>
                                )}

                            <div className="mt-6 space-y-4">
                                {inquiry.notes.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        Belum ada catatan internal.
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
                                                {formatDateTimeIndonesian(note.created_at)}
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
                                Alur Penanganan Pesan
                            </h2>

                            <p className="mt-3 text-sm text-muted-foreground">
                                Status Saat Ini
                            </p>

                            <p className="mt-1 text-xl font-bold text-cim-navy">
                                {humanizeStatus(inquiry.status)}
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
                                            label="Ubah Status Pesan"
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
                                                            {humanizeStatus(transition.value)}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </FormField>

                                        <FormField
                                            label={
                                                selectedTransition?.requires_reason
                                                    ? 'Alasan Pembukaan Kembali (Wajib diisi)'
                                                    : 'Alasan / Catatan Perubahan (Opsional)'
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
                                                placeholder="Tuliskan alasan atau keterangan perubahan status..."
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
                                                ? 'Memperbarui...'
                                                : 'Perbarui Status'}
                                        </Button>
                                    </form>
                                )}
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Riwayat Perubahan Status
                            </h2>

                            <ol className="mt-5 space-y-5 border-l pl-5">
                                {history.length === 0 ? (
                                    <li className="text-sm text-muted-foreground">
                                        Belum ada riwayat perubahan status.
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
                                                {event.from_status ? humanizeStatus(event.from_status) : 'Pesan Baru Diterima'}
                                                {' → '}
                                                {event.to_status ? humanizeStatus(event.to_status) : '—'}
                                            </p>

                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {event.actor?.name ?? 'Sistem Otomatis'}
                                                {' · '}
                                                {formatDateTimeIndonesian(event.created_at)}
                                            </p>

                                            {event.reason_note_id && (
                                                <p className="mt-2 text-xs text-muted-foreground">
                                                    Alasan tercatat pada catatan internal #{event.reason_note_id}.
                                                </p>
                                            )}
                                        </li>
                                    ))
                                )}
                            </ol>
                        </section>

                        <section className="rounded-2xl border bg-background p-6">
                            <h2 className="text-lg font-semibold text-cim-navy">
                                Penyelesaian & Moderasi
                            </h2>

                            <dl className="mt-4 space-y-4 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">
                                        Waktu Diselesaikan
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDateTimeIndonesian(inquiry.resolved_at) || '—'}
                                    </dd>
                                </div>

                                <div>
                                    <dt className="text-muted-foreground">
                                        Waktu Ditandai Spam
                                    </dt>

                                    <dd className="mt-1 font-medium">
                                        {formatDateTimeIndonesian(inquiry.spam_marked_at) || '—'}
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
                        ← Kembali ke Daftar Pesan Masuk
                    </Link>
                </div>
            </AdminLayout>
        </>
    );
}
