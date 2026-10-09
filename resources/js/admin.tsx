import '../css/admin.css';

import {
    createInertiaApp,
} from '@inertiajs/react';

import {
    createRoot,
} from 'react-dom/client';

import { type ComponentType } from 'react';

type PageModule = { default: ComponentType<Record<string, unknown>> };

const pages = import.meta.glob<PageModule>('./pages/**/*.tsx');

createInertiaApp({
    title: (title) => {
        return title
            ? `${title} | PT Cahya Intan Medika`
            : 'PT Cahya Intan Medika';
    },

    resolve: (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(
                `Inertia page "${name}" was not found.`,
            );
        }

        return page().then((mod) => mod.default);
    },

    setup({ el, App, props }) {
        createRoot(el).render(
            <App {...props} />,
        );
    },

    progress: {
        color: '#0077b6',
    },
});
