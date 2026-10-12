type Props = {
    title: string;
    description: string;
};

export function EmptyState({
    title,
    description,
}: Props) {
    return (
        <div className="rounded-2xl border border-dashed p-10 text-center">
            <h2 className="font-semibold text-cim-navy">
                {title}
            </h2>

            <p className="mt-2 text-sm text-muted-foreground">
                {description}
            </p>
        </div>
    );
}