import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export function NavigationLoadingBar() {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const removeStart = router.on(
            'start',
            () => setLoading(true),
        );

        const removeFinish = router.on(
            'finish',
            () => setLoading(false),
        );

        return () => {
            removeStart();
            removeFinish();
        };
    }, []);

    if (!loading) {
        return null;
    }

    return (
        <div
            className="fixed left-0 top-0 z-[100] h-1 w-full animate-pulse bg-cim-blue"
            aria-label="Navigation loading"
        />
    );
}