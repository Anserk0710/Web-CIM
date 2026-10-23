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

export default function Login() {
    const {
        flash,
    } = usePage<SharedPageProps>().props;

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post('/admin/login', {
            onFinish: () => form.reset(
                'password',
            ),
        });
    }

    return (
        <>
            <Head title="Masuk ke CMS" />

            <AuthLayout>
                <section className="rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <h1 className="text-2xl font-bold tracking-tight text-cim-navy">
                        Masuk ke CMS
                    </h1>

                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                        Gunakan akun internal Anda untuk mengakses panel pengelolaan.
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
                                autoComplete="username"
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

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-2 block text-sm font-medium"
                            >
                                Kata Sandi
                            </label>

                            <input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                value={form.data.password}
                                onChange={(event) => form.setData(
                                    'password',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                required
                            />
                        </div>

                        <label className="flex items-center gap-2 text-sm cursor-pointer">
                            <input
                                type="checkbox"
                                checked={form.data.remember}
                                className="rounded border-input text-cim-blue focus:ring-cim-blue"
                                onChange={(event) => form.setData(
                                    'remember',
                                    event.target.checked,
                                )}
                            />

                            Ingat sesi saya
                        </label>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={form.processing}
                        >
                            {form.processing
                                ? 'Memproses Masuk...'
                                : 'Masuk ke Sistem'}
                        </Button>
                    </form>

                    <div className="mt-6 text-center">
                        <Link
                            href="/admin/forgot-password"
                            className="text-sm font-medium text-cim-blue hover:underline"
                        >
                            Lupa kata sandi?
                        </Link>
                    </div>
                </section>
            </AuthLayout>
        </>
    );
}