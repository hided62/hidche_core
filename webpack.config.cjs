const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');

module.exports = (env, argv) => {
    const target = env.target ?? 'hwe';
    const mode = argv.mode ?? 'production';
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
            //v_test: resolve(__dirname, `${target}/ts/v_test.ts`)
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
                        chunks: 'all',
                    },
                },
            }
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
            alias: {
                '@': resolve(__dirname, `${target}/ts`)
            }
        },
        mode,
        entry: {
            chiefCenter: '@/chiefCenter.ts',
            inheritPoint: '@/inheritPoint.ts',
            common: '@/common_deprecated.ts',
            troop: '@/troop.ts',
            map: '@/map.ts',
            install_db: '@/install_db.ts',
            install: '@/install.ts',
            battle_simulator: '@/battle_simulator.ts',
            recent_map: '@/recent_map.ts',
            processing: '@/processing.ts',
            select_npc: '@/select_npc.ts',
            betting: '@/betting.ts',
            board: '@/board.ts',
            bossInfo: '@/bossInfo.ts',
            myPage: '@/myPage.ts',
            extExpandCity: '@/extExpandCity.ts',
            main: '@/main.ts',
            dipcenter: '@/dipcenter.ts',
            diplomacy: '@/diplomacy.ts',
            currentCity: '@/currentCity.ts',
            hallOfFame: '@/hallOfFame.ts',
            history: '@/history.ts',
            join: '@/join.ts',
            select_general_from_pool: '@/select_general_from_pool.ts',
            extKingdoms: '@/extKingdoms.ts',
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
            }
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
            alias: {
                '@': resolve(__dirname, `${target}/ts/gateway`)
            }
        },
        mode,
        entry: {
            'common': '@/common_deprecated.ts',
            'entrance': '@/entrance.ts',
            'user_info': '@/user_info.ts',
            'admin_member': '@/admin_member.ts',
            'join': '@/join.ts',
            'login': '@/login.ts',
            'install': '@/install.ts',
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
            }
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
        return [ingame_vue, ingame, gateway];
    }
    else {
        return [ingame_vue, ingame];
    }
}
