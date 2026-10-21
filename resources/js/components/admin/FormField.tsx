import { type ReactNode } from 'react';

type Props = {
    label: string;
    htmlFor?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    children: ReactNode;
};

export function FormField({
    label,
    htmlFor,
    error,
    hint,
    required = false,
    children,
}: Props) {
    return (
        <div>
            <label
                htmlFor={htmlFor}
                className="mb-2 block text-sm font-medium"
            >
                {label}

                {required && (
                    <span
                        className="ml-1 text-destructive"
                        aria-hidden="true"
                    >
                        *
                    </span>
                )}
            </label>

            {children}

            {hint && !error && (
                <p className="mt-2 text-xs text-muted-foreground">
                    {hint}
                </p>
            )}

            {error && (
                <p className="mt-2 text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}