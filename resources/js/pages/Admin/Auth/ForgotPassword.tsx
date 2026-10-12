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
            <Head title="Lupa Password" />

            <AuthLayout>
                <section className="rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Reset password
                    </h1>

                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                        Masukkan email akun Anda. Respons tidak mengungkap apakah akun terdaftar.
                    </p>

                    {flash.status && (
                        <div className="mt-5 rounded-lg bg-cim-ice p-3 text-sm text-cim-navy">
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
                                Email
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
                                className="w-full rounded-lg border border-input bg-background px-3 py-2"
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
                            Kirim tautan reset
                        </Button>
                    </form>

                    <Link
                        href="/admin/login"
                        className="mt-6 block text-center text-sm font-medium text-cim-blue hover:underline"
                    >
                        Kembali ke login
                    </Link>
                </section>
            </AuthLayout>
        </>
    );
}