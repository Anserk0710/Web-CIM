import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import AuthLayout from '@/layouts/AuthLayout';

import {
    type SharedPageProps,
} from '@/types';

export default function ForgotPassword() {
    const {
        flash,
    } = usePage<SharedPageProps>().props;

    const form = useForm({
        email: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post('/admin/forgot-password');
    }

    return (
        <>
            <Head title="Pemulihan Kata Sandi" />

            <AuthLayout>
                <section className="rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Atur Ulang Kata Sandi
                    </h1>

                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                        Masukkan alamat email akun pengelola Anda. Tautan pemulihan akan dikirimkan ke email tersebut.
                    </p>

                    {flash.status && (
                        <div className="mt-5 rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-900">
                            {flash.status}
                        </div>
                    )}

                    <form
                        onSubmit={submit}
                        className="mt-6 space-y-5"
                    >
                        <div>
                            <label
                                htmlFor="email"
                                className="mb-2 block text-sm font-medium"
                            >
                                Alamat Email
                            </label>

                            <input
                                id="email"
                                type="email"
                                autoComplete="email"
                                value={form.data.email}
                                onChange={(event) => form.setData(
                                    'email',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                required
                            />

                            {form.errors.email && (
                                <p className="mt-2 text-sm text-destructive">
                                    {form.errors.email}
                                </p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={form.processing}
                        >
                            {form.processing ? 'Mengirim...' : 'Kirim Tautan Pemulihan Kata Sandi'}
                        </Button>
                    </form>

                    <Link
                        href="/admin/login"
                        className="mt-6 block text-center text-sm font-medium text-cim-blue hover:underline"
                    >
                        Kembali ke halaman masuk
                    </Link>
                </section>
            </AuthLayout>
        </>
    );
}