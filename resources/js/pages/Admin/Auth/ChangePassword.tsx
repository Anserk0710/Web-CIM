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
            <Head title="Ganti Password" />

            <AdminLayout>
                <section className="max-w-xl rounded-2xl border bg-background p-6">
                    <h1 className="text-2xl font-bold text-cim-navy">
                        Ganti password
                    </h1>

                    {flash.success && (
                        <div className="mt-5 rounded-lg bg-cim-ice p-3 text-sm text-cim-navy">
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
                                Password saat ini
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
                                className="w-full rounded-lg border border-input px-3 py-2"
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
                                Password baru
                            </label>

                            <input
                                id="password"
                                type="password"
                                value={form.data.password}
                                onChange={(event) => form.setData(
                                    'password',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
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
                                value={
                                    form.data
                                        .password_confirmation
                                }
                                onChange={(event) => form.setData(
                                    'password_confirmation',
                                    event.target.value,
                                )}
                                className="w-full rounded-lg border border-input px-3 py-2"
                            />
                        </div>

                        <Button
                            type="submit"
                            disabled={form.processing}
                        >
                            Simpan password
                        </Button>
                    </form>
                </section>
            </AdminLayout>
        </>
    );
}