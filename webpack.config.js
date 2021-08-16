const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = [
    {
        name: 'ingame',
        resolve: {
            extensions: [".js", ".ts", ".tsx"]
        },
        entry: {
            chiefCenter: './hwe/ts/chiefCenter.ts',
            inheritPoint: './hwe/ts/inheritPoint.ts',
            common: './hwe/ts/common_deprecated.ts',
            troop: './hwe/ts/troop.ts',
            map: './hwe/ts/map.ts',
            install_db: './hwe/ts/install_db',
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, 'hwe/js'),
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
                filename: '../css/[name].css'
            })
        ]
    },
    {
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
            path: path.resolve(__dirname, 'js'),
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
                filename: '../css/[name].css'
            }),
        ]
    },
]
