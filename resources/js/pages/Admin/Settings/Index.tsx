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

const CONTACT_FIELD_LABELS: Record<keyof Settings['contact'], string> = {
    email: 'Alamat Email Resmi',
    phone: 'Nomor Telepon Kantor',
    whatsapp: 'Nomor WhatsApp Layanan',
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
            <Head title="Pengaturan Situs Web" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Pengaturan',
                    },
                ]}
            >
                <PageHeader
                    title="Pengaturan Situs Web"
                    description="Kelola profil perusahaan, kontak resmi, jangkauan operasional, branding, dan identitas situs."
                />

                <form
                    onSubmit={submit}
                    className="mt-8 space-y-6"
                >
                    <section className="rounded-xl border bg-background p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-cim-navy">
                            Profil Perusahaan
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField
                                label="Nama Badan Hukum Perusahaan"
                                htmlFor="legal_name"
                            >
                                <input
                                    id="legal_name"
                                    value={form.data.company.legal_name}
                                    onChange={(event) => form.setData('company', {
                                        ...form.data.company,
                                        legal_name: event.target.value,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>

                            <FormField
                                label="Nama Merek / Tampilan Publik"
                                htmlFor="display_name"
                            >
                                <input
                                    id="display_name"
                                    value={form.data.company.display_name}
                                    onChange={(event) => form.setData('company', {
                                        ...form.data.company,
                                        display_name: event.target.value,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>
                        </div>

                        <FormField
                            label="Tentang Perusahaan (Ringkasan Singkat)"
                            htmlFor="about"
                        >
                            <textarea
                                id="about"
                                value={form.data.company.about}
                                onChange={(event) => form.setData('company', {
                                    ...form.data.company,
                                    about: event.target.value,
                                })}
                                className="mt-5 min-h-32 w-full rounded-lg border border-input px-3 py-2 text-sm"
                            />
                        </FormField>
                    </section>

                    <section className="rounded-xl border bg-background p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-cim-navy">
                            Kontak & Wilayah Layanan
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
                                    label={CONTACT_FIELD_LABELS[field]}
                                >
                                    <input
                                        value={form.data.contact[field]}
                                        onChange={(event) => form.setData('contact', {
                                            ...form.data.contact,
                                            [field]: event.target.value,
                                        })}
                                        className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                    />
                                </FormField>
                            ))}
                        </div>

                        <FormField
                            label="Daftar Wilayah Jangkauan Layanan"
                            hint="Tuliskan satu nama wilayah per baris."
                        >
                            <textarea
                                value={form.data.coverage.regions.join('\n')}
                                onChange={(event) => form.setData('coverage', {
                                    regions: event.target.value
                                        .split('\n')
                                        .map((value) => value.trim())
                                        .filter(Boolean),
                                })}
                                className="mt-5 min-h-28 w-full rounded-lg border border-input px-3 py-2 text-sm"
                            />
                        </FormField>
                    </section>

                    <section className="rounded-xl border bg-background p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-cim-navy">
                            Identitas Visual & Optimasi Mesin Pencari (SEO)
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField label="Logo Utama Situs">
                                <select
                                    value={form.data.branding.logo_media_id ?? ''}
                                    onChange={(event) => form.setData('branding', {
                                        ...form.data.branding,
                                        logo_media_id: event.target.value ? Number(event.target.value) : null,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                >
                                    <option value="">Tidak dipilih (Gunakan teks nama merek)</option>
                                    {media.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <FormField label="Ikon Peramban (Favicon)">
                                <select
                                    value={form.data.branding.favicon_media_id ?? ''}
                                    onChange={(event) => form.setData('branding', {
                                        ...form.data.branding,
                                        favicon_media_id: event.target.value ? Number(event.target.value) : null,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                >
                                    <option value="">Tidak dipilih (Ikon standar)</option>
                                    {media.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <FormField label="Akhiran Judul Halaman (Title Suffix)">
                                <input
                                    value={form.data.seo_defaults.title_suffix}
                                    onChange={(event) => form.setData('seo_defaults', {
                                        ...form.data.seo_defaults,
                                        title_suffix: event.target.value,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>

                            <FormField label="Deskripsi Bawaan Mesin Pencari (Meta Description)">
                                <textarea
                                    value={form.data.seo_defaults.default_description}
                                    onChange={(event) => form.setData('seo_defaults', {
                                        ...form.data.seo_defaults,
                                        default_description: event.target.value,
                                    })}
                                    className="min-h-24 w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>
                        </div>
                    </section>

                    <section className="rounded-xl border bg-background p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-cim-navy">
                            Bagian Bawah Situs (Footer)
                        </h2>

                        <div className="mt-5 grid gap-5 md:grid-cols-2">
                            <FormField label="Slogan / Tagline Perusahaan">
                                <input
                                    value={form.data.footer.tagline}
                                    onChange={(event) => form.setData('footer', {
                                        ...form.data.footer,
                                        tagline: event.target.value,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>

                            <FormField label="Teks Hak Cipta (Copyright)">
                                <input
                                    value={form.data.footer.copyright}
                                    onChange={(event) => form.setData('footer', {
                                        ...form.data.footer,
                                        copyright: event.target.value,
                                    })}
                                    className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                />
                            </FormField>
                        </div>
                    </section>

                    {canUpdate && (
                        <div className="flex justify-end">
                            <Button
                                type="submit"
                                disabled={form.processing}
                            >
                                {form.processing ? 'Menyimpan...' : 'Simpan Pengaturan'}
                            </Button>
                        </div>
                    )}
                </form>

                <section className="mt-10">
                    <h2 className="text-xl font-bold text-cim-navy">
                        Pengaturan Halaman Statis
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Pilih halaman di bawah untuk mengubah susunan bagian dan isi teks:
                    </p>

                    <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {pages.map((page) => (
                            <Link
                                key={page.id}
                                href={`/admin/settings/pages/${page.id}`}
                                className="rounded-xl border bg-background p-4 font-medium transition-colors hover:border-cim-blue hover:shadow-sm"
                            >
                                <span className="block text-cim-navy font-semibold">{page.title}</span>
                                <span className="text-xs text-muted-foreground font-mono">/{page.key}</span>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="mt-10">
                    <h2 className="text-xl font-bold text-cim-navy">
                        Kantor & Cabang Operasional
                    </h2>

                    <div className="mt-4 space-y-3">
                        {offices.map((office) => (
                            <div
                                key={office.id}
                                className="rounded-xl border bg-background p-4 shadow-sm"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="font-semibold text-cim-navy">
                                            {office.name}
                                        </p>

                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {office.address}
                                        </p>
                                    </div>

                                    <div className="flex items-center gap-1.5 text-xs font-semibold">
                                        {office.is_head_office && (
                                            <span className="rounded-full bg-cim-ice px-2.5 py-0.5 text-cim-navy border border-cim-blue/30">
                                                Kantor Pusat
                                            </span>
                                        )}
                                        <span
                                            className={[
                                                'rounded-full px-2.5 py-0.5 border',
                                                office.is_active
                                                    ? 'border-green-300 bg-green-50 text-green-800'
                                                    : 'border-slate-300 bg-slate-100 text-slate-700',
                                            ].join(' ')}
                                        >
                                            {office.is_active ? 'Aktif' : 'Nonaktif'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}