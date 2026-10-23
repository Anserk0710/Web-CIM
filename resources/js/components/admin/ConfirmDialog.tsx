import { type ReactNode } from 'react';
import { Button } from '@/components/ui/button';

type Props = {
    open: boolean;
    title: string;
    description: string;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
    busy?: boolean;
    onConfirm: () => void;
    onCancel: () => void;
    children?: ReactNode;
};

export function ConfirmDialog({
    open,
    title,
    description,
    confirmLabel = 'Konfirmasi',
    cancelLabel = 'Batal',
    destructive = false,
    busy = false,
    onConfirm,
    onCancel,
    children,
}: Props) {
    if (!open) {
        return null;
    }

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            role="presentation"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) {
                    onCancel();
                }
            }}
        >
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="confirm-dialog-title"
                className="w-full max-w-md rounded-2xl bg-background p-6 shadow-xl"
            >
                <h2
                    id="confirm-dialog-title"
                    className="text-xl font-bold text-cim-navy"
                >
                    {title}
                </h2>

                <p className="mt-3 text-sm leading-6 text-muted-foreground">
                    {description}
                </p>

                {children && (
                    <div className="mt-4">
                        {children}
                    </div>
                )}

                <div className="mt-6 flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={busy}
                    >
                        {cancelLabel}
                    </Button>

                    <Button
                        type="button"
                        variant={
                            destructive
                                ? 'destructive'
                                : 'default'
                        }
                        onClick={onConfirm}
                        disabled={busy}
                    >
                        {busy
                            ? 'Memproses...'
                            : confirmLabel}
                    </Button>
                </div>
            </section>
        </div>
    );
}