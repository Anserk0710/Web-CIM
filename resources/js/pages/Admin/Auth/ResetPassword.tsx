import {
    Head,
    useForm,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import AuthLayout from '@/layouts/AuthLayout';

type ResetPasswordProps = {
    token: string;
    email: string;
};

export default function ResetPassword({
    token,
    email,
}: ResetPasswordProps) {
    const form = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.post('/admin/reset-password', {
            onFinish: () => form.reset(
                'password',
                'password_confirmation',
            ),
        });
    }

    return (
        <>
            <Head title="Buat Kata Sandi Baru" />

            <AuthLayout>
                <section className="rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Buat Kata Sandi Baru
                    </h1>

                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                        Silakan tentukan kata sandi baru yang kuat dan aman untuk akun Anda.
                    </p>

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
                                value={form.data.email}
                                onChange={(event) => form.setData(
                                    'email',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2 text-sm"
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
                                Kata Sandi Baru
                            </label>

                            <input
                                id="password"
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password}
                                onChange={(event) => form.setData(
                                    'password',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                required
                            />

                            {form.errors.password && (
                                <p className="mt-2 text-sm text-destructive">
                                    {form.errors.password}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password_confirmation"
                                className="mb-2 block text-sm font-medium"
                            >
                                Ulangi Kata Sandi Baru
                            </label>

                            <input
                                id="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                value={
                                    form.data
                                        .password_confirmation
                                }
                                onChange={(event) => form.setData(
                                    'password_confirmation',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                required
                            />
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={form.processing}
                        >
                            {form.processing ? 'Menyimpan...' : 'Simpan Kata Sandi Baru'}
                        </Button>
                    </form>
                </section>
            </AuthLayout>
        </>
    );
}