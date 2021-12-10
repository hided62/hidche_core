module.exports = {
  root: true,
  parser: "vue-eslint-parser",
  parserOptions: {
    "project": "./tsconfig.json"
  },
  ignorePatterns: ['*.test.ts', '.eslintrc.cjs', 'webpack.config.cjs', '*.js'],
  overrides: [{
    files: ['*.ts', '*.tsx', "*.vue"],
  }],
  plugins: [
    "@typescript-eslint",

  ],
  extends: [
    "eslint:recommended",
    'plugin:vue/essential',
    '@vue/typescript',
    "plugin:@typescript-eslint/eslint-recommended",
    "plugin:@typescript-eslint/recommended"
  ],
  rules: {
    '@typescript-eslint/no-floating-promises': 'error',
    "vue/no-multiple-template-root": "off",
    "vue/no-v-for-template-key": "off",
    "vue/multi-word-component-names": "off",//TODO: 삭제
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
}