module.exports = {
    env: {
        browser: true,
        es2021: true,
    },
    parser: "@typescript-eslint/parser",
    extends: [
        "eslint:recommended",
        "plugin:react/recommended",
        "prettier",
        "plugin:@typescript-eslint/recommended",
    ],
    overrides: [],
    parserOptions: {
        ecmaVersion: "latest",
        sourceType: "module",
    },
    plugins: ["react", "@typescript-eslint"],
    rules: {},
};
