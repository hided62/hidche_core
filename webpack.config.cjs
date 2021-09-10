const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');
const CleanTerminalPlugin = require('clean-terminal-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
module.exports = (env, argv) => {
    const target = env.target ?? 'hwe';
    const mode = argv.mode ?? 'production';
    const tsDir = resolve(__dirname, `${target}/ts/`);
    const build_exports = require(`${tsDir}/build_exports.json`);


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
                vue: "@vue/runtime-dom"
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
                new CssMinimizerPlugin(),
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: /@license/i,
                        },
                    },
                    extractComments: true,
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [
                //FROM `vue inspect` and some tweaks
                {
                    test: /\.(ts|tsx)$/,
                    //exclude: /(node_modules)/,
                    use: [
                        'babel-loader',
                        {
                            loader: 'ts-loader',
                            options: {
                                transpileOnly: true,
                                appendTsSuffixTo: [
                                    '\\.vue$'
                                ],
                                happyPackMode: false
                            }
                        }
                    ]
                },
                {
                    test: /\.js$/,
                    exclude: /(node_modules)/,
                    use: [
                        'babel-loader',
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
                    use: ['vue-style-loader', 'css-loader', 'sass-loader']
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
                },
            },
            minimizer: [
                new CssMinimizerPlugin(),
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: /@license/i,
                        },
                    },
                    extractComments: true,
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [{
                test: /\.(ts|tsx)$/i,
                exclude: /(node_modules)/,
                use: [{
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                "useBuiltIns": "usage",
                                "corejs": 3,
                                "modules": false
                            }],
                            '@babel/preset-typescript'
                        ]
                    }
                }, 'ts-loader']
            },
            {
                test: /.(s?[ac]ss)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
            }]
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
    };
    const gateway = {
        name: 'gateway',
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
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
                },
            },
            minimizer: [
                new CssMinimizerPlugin(),
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: /@license/i,
                        },
                    },
                    extractComments: true,
                }),
            ],
            moduleIds: 'deterministic',
        },
        module: {
            rules: [{
                test: /\.(ts|tsx)$/i,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                "useBuiltIns": "usage",
                                "corejs": 3,
                                "modules": false
                            }],
                            '@babel/preset-typescript'
                        ]
                    }
                }
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
