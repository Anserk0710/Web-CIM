import {
    Head,
} from '@inertiajs/react';

import {
    KeyRound,
    ShieldCheck,
    UserCheck,
} from 'lucide-react';

import AdminLayout from '@/layouts/AdminLayout';

type DashboardProps = {
    phase: string;
};

export default function Dashboard({
    phase,
}: DashboardProps) {
    return (
        <>
            <Head title="Dasbor CMS - PT Cahya Intan Medika" />

            <AdminLayout>
                <section>
                    <p className="text-sm font-semibold text-cim-blue">
                        Panel Kelola & Manajemen Konten
                    </p>

                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-cim-navy">
                        Selamat Datang di Panel CMS CIM
                    </h1>

                    <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground">
                        Kelola katalog produk alat kesehatan, buku e-katalog, informasi lowongan kerja,
                        pesan pertanyaan masuk, serta artikel kegiatan perusahaan dalam satu sistem terpadu yang aman.
                    </p>

                    <div className="mt-8 grid gap-4 md:grid-cols-3">
                        <article className="rounded-xl border bg-background p-5">
                            <UserCheck className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold text-cim-navy">
                                Keamanan & Akses Akun
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Akses sistem terlindungi dengan autentikasi ganda, pengelolaan kata sandi,
                                dan perlindungan sesi pengguna.
                            </p>
                        </article>

                        <article className="rounded-xl border bg-background p-5">
                            <ShieldCheck className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold text-cim-navy">
                                Pengaturan Peran Staf
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Hak akses setiap pengguna dibatasi secara otomatis sesuai wewenang dan tanggung jawab kerja.
                            </p>
                        </article>

                        <article className="rounded-xl border bg-background p-5">
                            <KeyRound className="size-5 text-cim-blue" />

                            <h2 className="mt-4 font-semibold text-cim-navy">
                                Riwayat & Catatan Aktivitas
                            </h2>

                            <p className="mt-2 text-sm text-muted-foreground">
                                Setiap perubahan penting pada data tercatat otomatis untuk menjaga transparansi dan keamanan informasi.
                            </p>
                        </article>
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}