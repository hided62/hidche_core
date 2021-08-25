const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');

module.exports = (env) => {
    const target = env.target??'hwe';
    const ingame = {
        name: 'ingame',
        resolve: {
            extensions: [".js", ".ts", ".tsx"],
            alias: {
                '@': resolve(__dirname, `${target}/ts`)
            }
        },
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


            //FORM 입력용, frontend 변경후 제거
            defaultSelectCityByMap: '@/defaultSelectCityByMap.ts',
            defaultSelectNationByMap: '@/defaultSelectNationByMap.ts',
            colorSelect: '@/colorSelect.ts',
            recruitCrewForm: '@/recruitCrewForm.ts'
        },
        output: {
            filename: '[name].js',
            path: resolve(__dirname, `${target}/dist_js`)
        },
        mode: 'production',
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
            }, {
                test: /\.vue$/i,
                exclude: /(node_modules)/,
                use: [
                    'vue-loader'
                ]
            }, {
                test: /\.css$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                ],
            }, {
                test: /\.s[ac]ss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    "sass-loader",
                ],
            }]
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
    const gateway = {
        name: 'gateway',
        resolve: {
            extensions: [".js", ".ts", ".tsx"]
        },
        entry: {
            'common': './ts/common_deprecated.ts',
            'entrance': './ts/entrance.ts',
            'user_info': './ts/user_info.ts',
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, 'dist_js'),
        },
        mode: 'production',
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
            },
            {
                test: /\.vue$/i,
                exclude: /(node_modules)/,
                use: [
                    { loader: 'vue-loader' }
                ]
            }, {
                test: /\.css$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                ],
            }, {
                test: /\.s[ac]ss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    "sass-loader",
                ],
            }]
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

    if(target == 'hwe'){
        return [ingame, gateway];
    }
    else{
        return [ingame];
    }
}
