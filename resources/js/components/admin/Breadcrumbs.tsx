import {
    Link,
} from '@inertiajs/react';

export type BreadcrumbItem = {
    label: string;
    href?: string;
};

type Props = {
    items: BreadcrumbItem[];
};

export function Breadcrumbs({
    items,
}: Props) {
    return (
        <nav
            aria-label="Breadcrumb"
            className="mb-5 flex flex-wrap gap-2 text-sm text-muted-foreground"
        >
            {items.map((item, index) => (
                <span
                    key={`${item.label}-${index}`}
                    className="flex items-center gap-2"
                >
                    {index > 0 && (
                        <span aria-hidden="true">
                            /
                        </span>
                    )}

                    {item.href ? (
                        <Link
                            href={item.href}
                            className="hover:text-cim-blue"
                        >
                            {item.label}
                        </Link>
                    ) : (
                        <span>
                            {item.label}
                        </span>
                    )}
                </span>
            ))}
        </nav>
    );
}