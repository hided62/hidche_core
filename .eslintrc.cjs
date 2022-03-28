module.exports = {
    root: true,
    parser: "vue-eslint-parser",
    "env": {
        "browser": true,
        "es2021": true,
        'vue/setup-compiler-macros': true
    },
    ignorePatterns: ['*.test.ts', '.eslintrc.cjs', 'postcss.config.cjs', 'webpack.config.cjs', '*.js'],
    overrides: [{
        files: ['*.ts', '*.tsx', "*.vue"],
    }],
    plugins: [
        "@typescript-eslint",
        "prettier"
    ],

    extends: [
        "eslint:recommended",
        "plugin:vue/vue3-recommended",
        "plugin:@typescript-eslint/recommended",
        "prettier",
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
        'vue/script-setup-uses-vars': 'error',
        'vue/max-attributes-per-line': [
            'warn',
            {
                singleline: 4, //prettier와 싸우지 말자
            },
        ],
        'vue/v-on-event-hyphenation': 'off', //vue3에선 필요없다고 생각
        'vue/attribute-hyphenation': 'off', //vue3에선 필요없다고 생각
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
