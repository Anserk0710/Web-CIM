import { useEffect, useState } from "react";

type Props = {
    message: string | null;
};

export function Toast({
    message,
}: Props) {
    const [visible, setVisible] = useState(
        Boolean(message),
    );

    useEffect(() => {
        setVisible(Boolean(message));

        if (!message) {
            return;
        }

        const timeout = window.setTimeout(
            () => setVisible(false),
            4000,
        );

        return () => window.clearTimeout(
            timeout,
        );
    }, [message]);

    if (!message || !visible) {
        return null;
    }

    return (
        <div
            role="status"
            className="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border bg-background px-5 py-4 text-sm shadow-lg"
        >
            {message}
        </div>
    );
}