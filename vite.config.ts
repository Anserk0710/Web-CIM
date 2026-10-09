import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/public.ts',
                'resources/js/admin.tsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],

    resolve: {
        alias: {
            '@': fileURLToPath(
                new URL('./resources/js', import.meta.url),
            ),
        },
    },

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'http://localhost:5173',

        cors: {
            origin: ['http://localhost:8080', 'http://localhost:5173'],
        },

        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws',
        },

        watch: {
            usePolling: true,
        },
    },
});