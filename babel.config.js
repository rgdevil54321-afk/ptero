module.exports = function (api) {
    let targets = {};
    const plugins = [

        'styled-components',
        '@babel/transform-runtime',
        '@babel/syntax-dynamic-import',
    ];

    if (api.env('test')) {
        targets = { node: 'current' };
        plugins.push('@babel/transform-modules-commonjs');
    }

    return {
        plugins,
        presets: [
            '@babel/typescript',
            ['@babel/env', {
                modules: false,
                useBuiltIns: 'entry',
                corejs: 3,
                targets,
            }],
            ['@babel/react', {
                runtime: 'automatic',
            }],
        ],
        compact: false,
    };
};
