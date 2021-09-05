const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');
const CleanTerminalPlugin = require('clean-terminal-webpack-plugin');

module.exports = (env, argv) => {
    const target = env.target ?? 'hwe';
    const mode = argv.mode ?? 'production';
    const tsDir = resolve(__dirname, `${target}/ts/`);
    const ingame_vue = {
        name: 'ingame_vue',
        resolve: {
            extensions: [".js", ".ts", ".jsx", ".tsx", ".vue"],
            alias: {
                vue: "@vue/runtime-dom"
            }
        },
        mode,
        entry: {
            v_inheritPoint: `${tsDir}/v_inheritPoint.ts`,
            v_board: `${tsDir}/v_board.ts`

        },
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
            ],
        },
        module: {
            rules: [
                //FROM `vue inspect` and some tweaks
                {
                    test: /\.(ts|tsx)$/,
                    exclude: /(node_modules)/,
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
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    exclude: /(node_modules)/,
                    options: {
                        /*transformAssetUrls: {
                            png: [],
                        },*/
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
                            loader: 'url-loader',
                            options: {
                                limit: 4096,
                                fallback: {
                                    loader: 'file-loader',
                                    options: {
                                        name: '../dist_misc/[name].[contenthash:8].[ext]'
                                    }
                                }
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
                            loader: 'url-loader',
                            options: {
                                limit: 4096,
                                fallback: {
                                    loader: 'file-loader',
                                    options: {
                                        name: '../dist_misc/[name].[contenthash:8].[ext]'
                                    }
                                }
                            }
                        }
                    ]
                },
                {
                    test: /\.(woff2?|eot|ttf|otf)$/i,
                    use: [
                        {
                            loader: 'url-loader',
                            options: {
                                limit: 4096,
                                fallback: {
                                    loader: 'file-loader',
                                    options: {
                                        name: '../dist_misc/[name].[contenthash:8].[ext]'
                                    }
                                }
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
            allowCollectingMemory: true,
        },
    };
    const ingame = {
        name: 'ingame',
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
        },
        mode,
        entry: {
            chiefCenter: `${tsDir}/chiefCenter.ts`,
            common: `${tsDir}/common_deprecated.ts`,
            troop: `${tsDir}/troop.ts`,
            map: `${tsDir}/map.ts`,
            install_db: `${tsDir}/install_db.ts`,
            install: `${tsDir}/install.ts`,
            battle_simulator: `${tsDir}/battle_simulator.ts`,
            recent_map: `${tsDir}/recent_map.ts`,
            processing: `${tsDir}/processing.ts`,
            select_npc: `${tsDir}/select_npc.ts`,
            betting: `${tsDir}/betting.ts`,
            board: `${tsDir}/legacy/board.ts`,
            bossInfo: `${tsDir}/bossInfo.ts`,
            myPage: `${tsDir}/myPage.ts`,
            extExpandCity: `${tsDir}/extExpandCity.ts`,
            main: `${tsDir}/main.ts`,
            dipcenter: `${tsDir}/dipcenter.ts`,
            diplomacy: `${tsDir}/diplomacy.ts`,
            currentCity: `${tsDir}/currentCity.ts`,
            hallOfFame: `${tsDir}/hallOfFame.ts`,
            history: `${tsDir}/history.ts`,
            join: `${tsDir}/join.ts`,
            select_general_from_pool: `${tsDir}/select_general_from_pool.ts`,
            extKingdoms: `${tsDir}/extKingdoms.ts`,
        },
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
            ],
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
                                "targets": "> 0.2%, not ie>8, not op_mini all",
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
            allowCollectingMemory: true,
        },
    };
    const gateway = {
        name: 'gateway',
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
        },
        mode,
        entry: {
            common: `${tsDir}/gateway/common_deprecated.ts`,
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
            ],
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
                                "targets": "> 0.2%, not ie>8, not op_mini all",
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
            allowCollectingMemory: true,
        },
    };

    if (target == 'hwe') {
        return [gateway, ingame_vue, ingame];
    }
    else {
        return [ingame_vue, ingame];
    }
}
