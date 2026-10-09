import {
    Head,
} from '@inertiajs/react';

import {
    LockKeyhole,
} from 'lucide-react';

import {
    Button,
} from '@/components/ui/button';

import AdminLayout from '@/layouts/AdminLayout';

type LoginProps = {
    phase: string;
};

export default function Login({
    phase,
}: LoginProps) {
    return (
        <>
            <Head title="CMS Login" />

            <AdminLayout>
                <section className="mx-auto max-w-lg rounded-2xl border bg-background p-6 shadow-sm sm:p-8">
                    <div className="mb-6 flex size-12 items-center justify-center rounded-xl bg-cim-ice text-cim-navy">
                        <LockKeyhole
                            aria-hidden="true"
                            className="size-6"
                        />
                    </div>

                    <p className="text-sm font-semibold text-cim-blue">
                        {phase}
                    </p>

                    <h1 className="mt-2 text-2xl font-bold tracking-tight text-cim-navy">
                        CMS Login Shell
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-muted-foreground">
                        Integrasi React, TypeScript, Inertia, Tailwind,
                        dan komponen CMS sudah aktif. Authentication
                        sesungguhnya akan dibuat pada Phase 2.
                    </p>

                    <form
                        className="mt-8 space-y-4"
                        onSubmit={(event) => event.preventDefault()}
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
                                disabled
                                placeholder="admin@example.com"
                                className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-2 block text-sm font-medium"
                            >
                                Password
                            </label>

                            <input
                                id="password"
                                type="password"
                                disabled
                                placeholder="••••••••"
                                className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm"
                            />
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled
                        >
                            Authentication tersedia di Phase 2
                        </Button>
                    </form>
                </section>
            </AdminLayout>
        </>
    );
}