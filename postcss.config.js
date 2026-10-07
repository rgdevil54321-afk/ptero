module.exports = {
    plugins: [
        require('@tailwindcss/postcss'),
        require('postcss-preset-env')({
            features: {
                'nesting-rules': false,
                'cascade-layers': false,
            },
        }),
    ],
};
