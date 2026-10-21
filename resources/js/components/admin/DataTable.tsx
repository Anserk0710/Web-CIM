import { type ReactNode } from 'react';
import { EmptyState } from '@/components/admin/EmptyState';

export type DataTableColumn<T> = {
    key: string;
    header: string;
    className?: string;
    render: (row: T) => ReactNode;
};

type Props<T> = {
    rows: T[];
    columns: DataTableColumn<T>[];
    rowKey: (row: T) => string | number;
    emptyTitle: string;
    emptyDescription: string;
};

export function DataTable<T>({
    rows,
    columns,
    rowKey,
    emptyTitle,
    emptyDescription,
}: Props<T>) {
    if (rows.length === 0) {
        return (
            <EmptyState
                title={emptyTitle}
                description={emptyDescription}
            />
        );
    }

    return (
        <div className="overflow-x-auto rounded-xl border bg-background">
            <table className="w-full min-w-[720px] text-left text-sm">
                <thead className="border-b bg-muted/50">
                    <tr>
                        {columns.map((column) => (
                            <th
                                key={column.key}
                                className={[
                                    'px-4 py-3 font-semibold',
                                    column.className ?? '',
                                ].join(' ')}
                            >
                                {column.header}
                            </th>
                        ))}
                    </tr>
                </thead>

                <tbody>
                    {rows.map((row) => (
                        <tr
                            key={rowKey(row)}
                            className="border-b last:border-0"
                        >
                            {columns.map((column) => (
                                <td
                                    key={column.key}
                                    className={[
                                        'px-4 py-3 align-top',
                                        column.className ?? '',
                                    ].join(' ')}
                                >
                                    {column.render(row)}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}