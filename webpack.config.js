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
            inheritPoint: './hwe/ts/inheritPoint.ts',
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, 'hwe/js'),
        },
        mode: 'production',
        devtool: 'source-map',
        module: {
            rules: [{
                test: /\.(ts|tsx)$/i,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            '@babel/preset-env',
                            '@babel/preset-typescript'
                        ]
                    }
                }
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
            //test: './ts/test.ts',
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, 'js'),
        },
        mode: 'production',
        devtool: 'source-map',
        module: {
            rules: [{
                test: /\.(ts|tsx)$/i,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            '@babel/preset-env',
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
