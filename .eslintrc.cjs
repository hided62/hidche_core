module.exports = {
    root: true,
    parser: "vue-eslint-parser",
    "env": {
        "browser": true,
        "es2021": true
    },
    ignorePatterns: ['*.test.ts', '.eslintrc.cjs', 'postcss.config.cjs', 'webpack.config.cjs', '*.js'],
    overrides: [{
        files: ['*.ts', '*.tsx', "*.vue"],
    }],
    plugins: [
        "@typescript-eslint"
    ],

    extends: [
        "eslint:recommended",
        "plugin:vue/vue3-essential",
        "plugin:@typescript-eslint/recommended"
    ],
    parserOptions: {
        "sourceType": "module",
        "project": "./tsconfig.json",
        "ecmaVersion": 2021,
        "parser": "@typescript-eslint/parser",
        extraFileExtensions: ['.vue']
    },
    rules: {
        '@typescript-eslint/no-floating-promises': 'error',
    },
    settings: {
        'import/resolver': {
            alias: {
                map: [
                    ['@', './hwe/ts'],
                    ['@util', './hwe/ts/util'],
                    ['@scss', './hwe/scss'],
                ]
            }
        }
    },
};
