const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');
const CleanTerminalPlugin = require('clean-terminal-webpack-plugin');
const { ESBuildMinifyPlugin } = require('esbuild-loader')
const webpack = require('webpack');
module.exports = (env, argv) => {
    const target = env.target ?? 'hwe';
    const mode = argv.mode ?? 'production';
    const tsDir = resolve(__dirname, `${target}/ts/`);
    const build_exports = require(`${tsDir}/build_exports.json`);

    //TODO: esbuild에 browserslist 사용 가능하면 적용

    //서버마다 ts 파일 구성이 다를 가능성이 높기 때문에 어떤 파일이 필요한지는 ts/build_exports.json을 확인한다.
    const entryIngameVue = {};
    for (const [entry, filePath] of Object.entries(build_exports.ingame_vue) ?? []) {
        entryIngameVue[entry] = `${tsDir}/${filePath}`;
    }
    const entryIngame = {};
    for (const [entry, filePath] of Object.entries(build_exports.ingame) ?? []) {
        entryIngame[entry] = `${tsDir}/${filePath}`;
    }

    const ingame_vue = {
        name: `ingame_${target}_vue`,
        resolve: {
            extensions: [".ts", ".tsx", ".vue", ".js"],
            alias: {
                vue: "@vue/runtime-dom",
                '@': tsDir,
                '@scss': path.resolve(tsDir, '../scss'),
                '@util': path.resolve(tsDir, 'util'),
            }
        },
        mode,
        entry: entryIngameVue,
        output: {
            filename: '[name].js',
            path: resolve(__dirname, `${target}/dist_js`)
        },
        devtool: 'source-map',
        optimization: {
            splitChunks: {
                cacheGroups: {
                    commons: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors_vue',
                        priority: -10,
                        chunks: 'all',
                        reuseExistingChunk: true,
                    },
                    default: {
                        name: 'common_vue',
                        minChunks: 2,
                        priority: -20,
                        chunks: 'all',
                        reuseExistingChunk: true,
                    },
                },
            },
            minimizer: [
                new ESBuildMinifyPlugin({
                    css: true
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [
                //FROM `vue inspect` and some tweaks
                {
                    test: /\.ts$/,
                    //exclude: /(node_modules)/,
                    use: [
                        {
                            loader: 'esbuild-loader',
                            options: {
                                loader: 'ts',
                                target: 'es2019',
                            }
                        }
                    ]
                },
                {
                    test: /\.tsx$/,
                    //exclude: /(node_modules)/,
                    use: [
                        {
                            loader: 'esbuild-loader',
                            options: {
                                loader: 'tsx',
                                target: 'es2019',
                            }
                        }
                    ]
                },
                {
                    test: /\.js$/,
                    exclude: /(node_modules)/,
                    use: [
                        {
                            loader: 'esbuild-loader',
                            options: {
                                loader: 'js',
                                target: 'es2019',
                            }
                        }
                    ]
                },
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    exclude: /(node_modules)/,
                    options: {
                        hotReload: false,
                    }
                },
                {
                    test: /\.vue\.(s?[ac]ss)$/,
                    use: ['vue-style-loader', 'css-loader', {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'css',
                            minify: true
                        }
                    }, 'sass-loader']
                },
                {
                    test: /(?<!\.vue)\.(s?[ac]ss)$/,
                    use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
                },
                {
                    test: /\.(png|jpe?g|gif|webp)$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '../dist_misc/[name].[contenthash:8].[ext]'
                            }
                        }
                    ]
                },
                {
                    test: /\.(svg)$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '../dist_misc/[name].[contenthash:8].[ext]'
                            }
                        }
                    ]
                },
                {
                    test: /\.(mp4|webm|ogg|mp3|wav|flac|aac)$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '../dist_misc/[name].[contenthash:8].[ext]'
                            }
                        }
                    ]
                },
                {
                    test: /\.(woff2?|eot|ttf|otf)$/i,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '../dist_misc/[name].[contenthash:8].[ext]'
                            }
                        }
                    ]
                },
            ]
        },
        plugins: [
            new CleanTerminalPlugin(),
            new VueLoaderPlugin(),
            new MiniCssExtractPlugin({
                filename: '../dist_css/[name].css'
            }),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
        },
    };
    const ingame = {
        name: `ingame_${target}`,
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
            alias: {
                '@': tsDir,
                '@scss': path.resolve(tsDir, '../scss'),
                '@util': path.resolve(tsDir, 'util'),
            },
        },
        mode,
        entry: entryIngame,
        output: {
            filename: '[name].js',
            path: resolve(__dirname, `${target}/dist_js`)
        },
        devtool: 'source-map',
        optimization: {
            splitChunks: {
                cacheGroups: {
                    commons: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                    },
                    default: {
                        name: 'common_ts',
                        minChunks: 2,
                        priority: -20,
                        chunks: 'all',
                        reuseExistingChunk: true,
                    },
                },
            },
            minimizer: [
                new ESBuildMinifyPlugin({
                    css: true
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [{
                test: /\.ts$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'ts',
                            target: 'es2019',
                        }
                    }
                ]
            },
            {
                test: /\.tsx$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'tsx',
                            target: 'es2019',
                        }
                    }
                ]
            },
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'js',
                            target: 'es2019',
                        }
                    }
                ]
            },
            {
                test: /.(s?[ac]ss)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
            },]
        },
        plugins: [
            new CleanTerminalPlugin(),
            new MiniCssExtractPlugin({
                filename: '../dist_css/[name].css'
            }),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
        },
        externals: {
            // require("jquery") is external and available
            //  on the global var jQuery
            "jQuery": "jquery"
        }
    };
    const gateway = {
        name: 'gateway',
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
            alias: {
                '@': tsDir,
                '@scss': path.resolve(tsDir, '../scss'),
                '@util': path.resolve(tsDir, 'util'),
            },
        },
        mode,
        entry: {
            entrance: `${tsDir}/gateway/entrance.ts`,
            user_info: `${tsDir}/gateway/user_info.ts`,
            admin_member: `${tsDir}/gateway/admin_member.ts`,
            join: `${tsDir}/gateway/join.ts`,
            login: `${tsDir}/gateway/login.ts`,
            install: `${tsDir}/gateway/install.ts`,
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, 'dist_js'),
        },
        devtool: 'source-map',
        optimization: {
            splitChunks: {
                cacheGroups: {
                    commons: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                    },
                    default: {
                        name: 'common_ts',
                        minChunks: 2,
                        priority: -20,
                        chunks: 'all',
                        reuseExistingChunk: true,
                    },
                },
            },
            minimizer: [
                new ESBuildMinifyPlugin({
                    css: true
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [{
                test: /\.ts$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'ts',
                            target: 'es2019',
                        }
                    }
                ]
            },
            {
                test: /\.tsx$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'tsx',
                            target: 'es2019',
                        }
                    }
                ]
            },
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'js',
                            target: 'es2019',
                        }
                    }
                ]
            }, {
                test: /.(s?[ac]ss)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
            }]
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: '../dist_css/[name].css'
            }),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
        },
    };

    if (target == 'hwe') {
        return [gateway, ingame_vue, ingame];
    }
    else {
        return [ingame_vue, ingame];
    }
}
