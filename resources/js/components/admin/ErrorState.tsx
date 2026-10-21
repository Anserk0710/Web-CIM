type Props = {
    title?: string;
    message: string;
};

export function ErrorState({
    title = 'Something Went Wrong',
    message,
}: Props) {
    return (
        <section
            role="alert"
            className="rounded-xl border border-destructive/30 bg-destructive/5 p-5"
        >
            <h2 className="font-semibold text-destructive">
                {title}
            </h2>

            <p className="mt-2 text-sm text-destructive">
                {message}
            </p>
        </section>
    );
}