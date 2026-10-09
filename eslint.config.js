import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/build/**',
        ],
    },
    ...tseslint.configs.recommended,
    {
        files: ['resources/js/**/*.{ts,tsx}'],
        languageOptions: {
            globals: globals.browser,
        },
    },
);