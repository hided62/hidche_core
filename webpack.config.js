const path = require('path');

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
                test: /\.(ts|tsx)$/,
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
            }]
        },
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
        mode: 'none',
        devtool: 'source-map',
        module: {
            rules: [{
                test: /\.(ts|tsx)$/,
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
            }]
        },
    },
]
