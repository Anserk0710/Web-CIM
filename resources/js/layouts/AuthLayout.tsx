import {
    type PropsWithChildren,
} from 'react';

export default function AuthLayout({
    children,
}: PropsWithChildren) {
    return (
        <main className="flex min-h-screen items-center justify-center bg-muted/50 px-4 py-12">
            <div className="w-full max-w-md">
                <div className="mb-6 text-center">
                    <p className="text-sm font-semibold text-cim-blue">
                        CIM CMS
                    </p>

                    <p className="mt-1 text-sm text-muted-foreground">
                        PT Cahya Intan Medika
                    </p>
                </div>

                {children}
            </div>
        </main>
    );
}