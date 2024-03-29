const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { resolve } = require('path');
const CleanTerminalPlugin = require('clean-terminal-webpack-plugin');
const { EsbuildPlugin } = require('esbuild-loader')
const webpack = require('webpack');
const fs = require('fs');
module.exports = (env, argv) => {
    const rawTarget = env.target ?? 'hwe';
    const target = (rawTarget == 'gateway') ? 'hwe' : rawTarget;
    const mode = argv.mode ?? 'production';
    const tsDir = resolve(__dirname, `${target}/ts/`);
    const scssDir = resolve(__dirname, `${target}/scss/`);
    const build_exports = require(`${tsDir}/build_exports.json`);

    const versionGitPath = resolve(__dirname, target, 'd_setting', 'VersionGit.json');

    const versionValue = (() => {
        if (!fs.existsSync(versionGitPath)) {
            return undefined;
        }
        const versionInfo = JSON.parse(fs.readFileSync(versionGitPath, 'utf-8'));
        return versionInfo.versionGit;
    })()
    const versionTarget = versionValue ?? `${target}_dynamic`;
    const outputPath = resolve(__dirname, 'dist_js', versionTarget);
    fs.mkdirSync(outputPath, {
        recursive: true
    });

    const genBuildHook = function (oTarget) {
        const checkFilePath = resolve(outputPath, `build_${oTarget}.txt`);
        let emitDone = false;
        let writeDone = false;
        return function (percentage, msg, ...args) {
            if (msg == 'emitting') {
                emitDone = true;
            }
            if (percentage == 0) {
                if (fs.existsSync(checkFilePath)) {
                    fs.unlinkSync(checkFilePath);
                }
            } else if (percentage == 1 && emitDone && !writeDone) {
                fs.writeFileSync(checkFilePath, new Date().toISOString(), 'utf-8');
                writeDone = true;
            }
        };
    };

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
    entryIngameVue['bootstrap'] = `${scssDir}/bootstrap.scss`;
    const cacheDirectory = path.resolve(__dirname, 'node_modules/.cache/webpack');

    //const devtool = mode != 'production' ? 'eval-source-map' : (env.WEBPACK_WATCH ? 'source-map' : undefined);
    const devtool = mode != 'production' ? 'eval-source-map' : 'source-map';

    const optimization = {
        splitChunks: {
            cacheGroups: {
                commons: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendors',
                    priority: -10,
                    chunks: 'all',
                    reuseExistingChunk: true,
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
            new EsbuildPlugin({
                css: true
            }),
        ],
        moduleIds: 'deterministic',
    };

    const performance = {
        maxAssetSize: 5 * 1024 * 1024,
        maxEntrypointSize: 3 * 1024 * 1024,
    }

    const ingame_vue = {
        name: `ingame_${versionTarget}_vue`,
        resolve: {
            extensions: [".ts", ".tsx", ".vue", ".js"],
            alias: {
                vue: "@vue/runtime-dom",
                '@': tsDir,
                '@scss': path.resolve(tsDir, '../scss'),
                '@util': path.resolve(tsDir, 'util'),
            },
        },
        mode,
        entry: entryIngameVue,
        output: {
            filename: '[name].js',
            path: resolve(outputPath, 'vue')
        },
        devtool,
        optimization,
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
                                target: 'es2021',
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
                                target: 'es2021',
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
                                target: 'es2021',
                            }
                        }
                    ]
                },
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    //exclude: /(node_modules)/,
                    options: {
                        hotReload: false,
                    }
                },
                {
                    test: /\.vue\.(s?[ac]ss)$/,
                    use: ['vue-style-loader', 'css-loader', 'postcss-loader', 'sass-loader']
                },
                {
                    test: /(?<!\.vue)\.(s?[ac]ss)$/,
                    use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader']
                },
                {
                    test: /\.(png|jpe?g|gif|webp)$/,
                    use: ['file-loader']
                },
                {
                    test: /\.(svg)$/,
                    use: ['file-loader']
                },
                {
                    test: /\.(mp4|webm|ogg|mp3|wav|flac|aac)$/,
                    use: ['file-loader']
                },
                {
                    test: /\.(woff2?|eot|ttf|otf)$/i,
                    use: ['file-loader']
                },
            ]
        },
        plugins: [
            new CleanTerminalPlugin(),
            new VueLoaderPlugin(),
            new MiniCssExtractPlugin(),
            new webpack.ProgressPlugin({
                percentBy: 'modules',
                dependencies: false,
                handler: genBuildHook('vue')
            }),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
            cacheDirectory,
            cacheLocation: path.resolve(cacheDirectory, `ingame_vue_${mode}`)
        },
        performance,
    };
    const ingame = {
        name: `ingame_${versionTarget}`,
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
            path: resolve(outputPath, 'ts')
        },
        devtool,
        optimization,
        module: {
            rules: [{
                test: /\.ts$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'ts',
                            target: 'es2021',
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
                            target: 'es2021',
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
                            target: 'es2021',
                        }
                    }
                ]
            },
            {
                test: /.(s?[ac]ss)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader']
            },]
        },
        plugins: [
            new CleanTerminalPlugin(),
            new MiniCssExtractPlugin(),
            new webpack.ProgressPlugin(genBuildHook('ts')),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
            cacheDirectory,
            cacheLocation: path.resolve(cacheDirectory, `ingame_ts_${mode}`)
        },
        performance,
    };
    const gateway = {
        name: `gateway`,
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
            path: resolve(__dirname, 'dist_js', 'gateway')
        },
        devtool,
        optimization,
        module: {
            rules: [{
                test: /\.ts$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        loader: 'esbuild-loader',
                        options: {
                            loader: 'ts',
                            target: 'es2021',
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
                            target: 'es2021',
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
                            target: 'es2021',
                        }
                    }
                ]
            }, {
                test: /.(s?[ac]ss)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader']
            }]
        },
        plugins: [
            new MiniCssExtractPlugin(),
            new webpack.ProgressPlugin(genBuildHook('gateway')),
            //new BundleAnalyzerPlugin()
        ],
        cache: {
            type: 'filesystem',
            cacheDirectory,
            cacheLocation: path.resolve(cacheDirectory, `gateway_ts_${mode}`)
        },
        performance,
    };

    const buildConfList = [];
    if (rawTarget == 'gateway') {
        buildConfList.push(gateway);
        return buildConfList;
    }

    if (env.WEBPACK_WATCH || !versionValue) {
        return [gateway, ingame_vue, ingame];
    }



    if (target == 'hwe' && !fs.existsSync(resolve(outputPath, `build_gateway.txt`))) {
        buildConfList.push(gateway);
    }

    if (!fs.existsSync(resolve(outputPath, `build_vue.txt`))) {
        buildConfList.push(ingame_vue);
    }

    if (!fs.existsSync(resolve(outputPath, `build_ts.txt`))) {
        buildConfList.push(ingame);
    }

    return buildConfList;
}
