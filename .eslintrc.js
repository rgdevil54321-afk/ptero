module.exports = {
    parser: '@typescript-eslint/parser',
    parserOptions: {
        ecmaVersion: 6,
        ecmaFeatures: {
            jsx: true,
        },
        project: './tsconfig.json',
        tsconfigRootDir: __dirname,
    },
    settings: {
        react: {
            pragma: 'React',
            version: 'detect',
        },
        linkComponents: [
            { name: 'Link', linkAttribute: 'to' },
            { name: 'NavLink', linkAttribute: 'to' },
        ],
    },
    env: {
        browser: true,
        es6: true,
    },
    plugins: ['react', 'react-hooks', 'prettier', '@typescript-eslint'],
    extends: [
        'eslint:recommended',
        'plugin:react/recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:jest-dom/recommended',
    ],
    rules: {
        eqeqeq: 'error',
        'prettier/prettier': ['error', {}, { usePrettierrc: true }],
        'react/prop-types': 0,
        'react/display-name': 0,
        '@typescript-eslint/no-explicit-any': 0,
        '@typescript-eslint/no-non-null-assertion': 0,
        'no-use-before-define': 0,
        '@typescript-eslint/no-use-before-define': 'warn',
        '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
        '@typescript-eslint/ban-ts-comment': ['error', { 'ts-expect-error': 'allow-with-description' }],
        'react/jsx-no-literals': 'off',
    },
    overrides: [
        {
            files: ['DGEN/themes/**/*.{ts,tsx}', 'DGEN/addons/**/*.{ts,tsx}'],
            rules: {
                'no-console': ['error', { allow: ['warn', 'error'] }],
                'no-alert': 'error',
                'react/jsx-no-literals': [
                    'warn',
                    {
                        noStrings: true,
                        ignoreProps: true,
                        allowedStrings: [
                            ' ',
                            '-',
                            '–',
                            '—',
                            '/',
                            '|',
                            ':',
                            ',',
                            '.',
                            '•',
                            '%',
                            '×',
                            '·',
                        ],
                    },
                ],
            },
        },
        {
            files: ['resources/**/*.{ts,tsx}'],
            rules: {
                'react/jsx-no-literals': 'off',
            },
        },
        {
            files: ['**/*.test.{ts,tsx}', '**/__tests__/**/*.{ts,tsx}'],
            rules: {
                'react/jsx-no-literals': 'off',
            },
        },
        {
            files: ['DGEN/themes/Hyperv2/components/server/console/Console.tsx'],
            rules: {
                'no-console': 'off',
            },
        },
    ],
};
