const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
        "./resources/js/**/*.tsx",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter var", ...defaultTheme.fontFamily.sans],
            },
            transitionProperty: {
                height: "height",
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
        require("@tailwindcss/line-clamp"),
        require("@tailwindcss/aspect-ratio"),
    ],
};
