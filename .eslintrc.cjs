module.exports = {
    root: true,
    parser: "@typescript-eslint/parser",
    parserOptions: {
      "project": "./tsconfig.json"
    },
    ignorePatterns: ['*.test.ts', '.eslintrc.cjs', 'webpack.config.cjs', '*.js'],
    overrides: [{
      files: ['*.ts', '*.tsx'],
    }],
    plugins: [
      "@typescript-eslint",
    ],
    extends: [
      "eslint:recommended",
      "plugin:@typescript-eslint/eslint-recommended",
      "plugin:@typescript-eslint/recommended"
    ],
    rules: {
      '@typescript-eslint/no-floating-promises': 'error',
    }
  }