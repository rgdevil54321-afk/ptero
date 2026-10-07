const path = require('node:path');
const webpack = require('webpack');
const { WebpackAssetsManifest } = require('webpack-assets-manifest');
const TerserPlugin = require('terser-webpack-plugin');
const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    cache: true,
    target: 'web',
    mode: isProduction ? 'production' : 'development',
    devtool: process.env.DEVTOOL || (isProduction ? false : 'eval-source-map'),
    performance: {
        hints: false,
    },
    entry: ['./DGEN/themes/Hyperv2/entry.tsx'],
    output: {
        path: path.join(__dirname, '/public/assets'),
        filename: isProduction ? 'bundle.[chunkhash:8].js' : 'bundle.[fullhash:8].js',
        chunkFilename: isProduction ? '[name].[chunkhash:8].js' : '[name].[fullhash:8].js',
        publicPath: process.env.WEBPACK_PUBLIC_PATH || '/assets/',
        crossOriginLoading: 'anonymous',
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                exclude: /node_modules(?!\/ogl)|\.spec\.tsx?$/,
                loader: 'babel-loader',
            },
            {
                test: /\.c?js$/,
                include: /node_modules\/(ogl|tailwind-merge|motion|framer-motion|motion-dom|pathe)/,
                loader: 'babel-loader',
            },
            {
                test: /\.mjs$/,
                include: /node_modules/,
                type: 'javascript/auto',
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                modules: false,
                                useBuiltIns: 'entry',
                                corejs: 3,
                                targets: {
                                    browsers: ['> 0.5%', 'last 2 versions', 'firefox esr', 'not dead']
                                }
                            }]
                        ],
                    }
                }
            },
            {
                test: /\.css$/,
                use: [
                    { loader: 'style-loader' },
                    {
                        loader: 'css-loader',
                        options: {
                            modules: {
                                auto: true,
                                namedExport: false,
                                exportLocalsConvention: 'as-is',
                                localIdentName: isProduction ? '[name]_[hash:base64:8]' : '[path][name]__[local]',
                                localIdentContext: path.join(__dirname, 'DGEN/themes/Hyperv2/components'),
                            },
                            sourceMap: !isProduction,
                            importLoaders: 1,
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: { sourceMap: !isProduction },
                    },
                ],
            },
            {
                test: /\.(png|jp(e?)g|gif|avif)$/,
                type: 'asset/resource',
                generator: {
                    filename: 'images/[name].[hash:8][ext]',
                },
            },
            {
                test: /\.(woff|woff2)$/i,
                type: 'asset/resource',
            },
            {
                test: /\.svg$/,
                loader: 'svg-url-loader',
            },
            {
                test: /\.js$/,
                enforce: 'pre',
                loader: 'source-map-loader',
            },
        ],
    },
    stats: {
        errorDetails: true,
    },
    ignoreWarnings: [/Failed to parse source map/],
    resolve: {
        extensions: ['.ts', '.tsx', '.js', '.json', '.mjs'],
        alias: {
            '@': path.join(__dirname, '/DGEN/themes/Hyperv2'),
            '@DGEN': path.join(__dirname, '/DGEN'),
            '@definitions': path.join(__dirname, '/DGEN/themes/Hyperv2/api/definitions'),
            'motion/react': path.resolve(__dirname, 'node_modules/motion/dist/cjs/react.js'),
            'tailwind-merge': path.resolve(__dirname, 'node_modules/tailwind-merge/dist/bundle-mjs.mjs'),
            'state-local': path.resolve(__dirname, 'node_modules/state-local/lib/cjs/state-local.js'),
            'state-local/lib/es/state-local.js': path.resolve(
                __dirname,
                'node_modules/state-local/lib/cjs/state-local.js'
            ),
        },
        symlinks: false,
    },
    externals: {
        moment: 'moment',
    },
    plugins: [
        new webpack.EnvironmentPlugin({
            NODE_ENV: process.env.NODE_ENV || 'development',
            DEBUG: process.env.NODE_ENV !== 'production',
            WEBPACK_BUILD_HASH: Date.now().toString(16),
        }),
        new WebpackAssetsManifest({
            output: 'manifest.json',
            writeToDisk: true,
            publicPath: true,
            integrity: true,
            integrityHashes: ['sha384'],
        }),
        new MonacoWebpackPlugin({
            languages: [
                'javascript',
                'typescript',
                'json',
                'css',
                'html',
                'python',
                'php',
                'java',
                'csharp',
                'cpp',
                'ruby',
                'go',
                'rust',
                'sql',
                'shell',
                'yaml',
                'xml',
                'markdown',
                'lua',
            ],
            features: [
                '!accessibilityHelp',
                '!bracketMatching',
                '!caretOperations',
                '!clipboard',
                '!comment',
                '!contextmenu',
                '!coreCommands',
                '!cursorUndo',
                '!dnd',
                '!find',
                '!folding',
                '!format',
                '!hover',
                '!inPlaceReplace',
                '!inspectTokens',
                '!iPadShowKeyboard',
                '!linesOperations',
                '!linkedEditing',
                '!links',
                '!multicursor',
                '!parameterHints',
                '!quickCommand',
                '!quickHelp',
                '!quickOutline',
                '!referenceSearch',
                '!rename',
                '!smartSelect',
                '!suggest',
                '!toggleHighContrast',
                '!walkThrough',
                '!viewportSemanticTokens',
            ],
        }),
    ],
    optimization: {
        usedExports: true,
        sideEffects: false,
        runtimeChunk: false,
        removeEmptyChunks: true,
        minimize: isProduction,
        minimizer: [
            new TerserPlugin({
                parallel: true,
                extractComments: false,
                terserOptions: {
                    mangle: true,
                    output: {
                        comments: false,
                    },
                },
            }),
        ],
    },
    watchOptions: {
        poll: 1000,
        ignored: /node_modules/,
    },
    devServer: {
        compress: true,
        port: 5173,
        server: {
            type: 'https',
            options: process.env.USE_LOCAL_CERTS
                ? {
                    ca: path.join(__dirname, '../../docker/certificates/root_ca.pem'),
                    cert: path.join(__dirname, '../../docker/certificates/pterodactyl.test.pem'),
                    key: path.join(__dirname, '../../docker/certificates/pterodactyl.test-key.pem'),
                }
                : undefined,
        },
        static: {
            directory: path.join(__dirname, '/public'),
            publicPath: process.env.WEBPACK_PUBLIC_PATH || '/assets/',
        },
        allowedHosts: ['.pterodactyl.test'],
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
    },
};