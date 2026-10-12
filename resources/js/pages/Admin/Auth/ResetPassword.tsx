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
            <Head title="Reset Password" />

            <AuthLayout>
                <section className="rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Buat password baru
                    </h1>

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
                                value={form.data.email}
                                onChange={(event) => form.setData(
                                    'email',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
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
                                Password baru
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
                                className="w-full rounded-lg border border-input px-3 py-2"
                                required
                            />
                        </div>

                        <div>
                            <label
                                htmlFor="password_confirmation"
                                className="mb-2 block text-sm font-medium"
                            >
                                Konfirmasi password
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
                                className="w-full rounded-lg border border-input px-3 py-2"
                                required
                            />
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={form.processing}
                        >
                            Simpan password baru
                        </Button>
                    </form>
                </section>
            </AuthLayout>
        </>
    );
}