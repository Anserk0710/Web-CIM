import {
    type ReactNode,
} from 'react';

type Props = {
    title: string;
    description?: string;
    actions?: ReactNode;
};

export function PageHeader({
    title,
    description,
    actions,
}: Props) {
    return (
        <header className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 className="text-3xl font-bold tracking-tight text-cim-navy">
                    {title}
                </h1>

                {description && (
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>

            {actions}
        </header>
    );
}