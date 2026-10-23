import {
    Head,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    type FormEvent,
} from 'react';

import {
    Button,
} from '@/components/ui/button';

import AdminLayout from '@/layouts/AdminLayout';

import {
    type SharedPageProps,
} from '@/types';

export default function ChangePassword() {
    const {
        flash,
    } = usePage<SharedPageProps>().props;

    const form = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function submit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        form.put('/admin/change-password', {
            onSuccess: () => form.reset(),
        });
    }

    return (
        <>
            <Head title="Ganti Kata Sandi" />

            <AdminLayout
                breadcrumbs={[
                    {
                        label: 'Ganti Kata Sandi',
                    },
                ]}
            >
                <section className="max-w-xl rounded-2xl border bg-background p-6 shadow-sm">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Ganti Kata Sandi Akun
                    </h1>

                    <p className="mt-1 text-sm text-muted-foreground">
                        Pastikan menggunakan kombinasi kata sandi yang aman untuk melindungi akses akun pengelola Anda.
                    </p>

                    {flash.success && (
                        <div className="mt-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-900">
                            {flash.success}
                        </div>
                    )}

                    <form
                        onSubmit={submit}
                        className="mt-6 space-y-5"
                    >
                        <div>
                            <label
                                htmlFor="current_password"
                                className="mb-2 block text-sm font-medium"
                            >
                                Kata Sandi Saat Ini
                            </label>

                            <input
                                id="current_password"
                                type="password"
                                value={
                                    form.data
                                        .current_password
                                }
                                onChange={(event) => form.setData(
                                    'current_password',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2 text-sm"
                                required
                            />

                            {form.errors.current_password && (
                                <p className="mt-2 text-sm text-destructive">
                                    {
                                        form.errors
                                            .current_password
                                    }
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
                            disabled={form.processing}
                        >
                            {form.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi'}
                        </Button>
                    </form>
                </section>
            </AdminLayout>
        </>
    );
}