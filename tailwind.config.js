import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

import animations from "./resources/js/modules/animations";
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/modules/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: colors.blue,
                secondary: colors.slate,
                green: colors.emerald,
                cool: colors.slate,
                dark: {
                    100: "#f0f1f2",
                    200: "#d2d4d7",
                    300: "#a7aaad",
                    400: "#6c7075",
                    500: "#4a4e53",
                    600: "#33373c",
                    700: "#262a2f",
                    800: "#1C1F24",
                    900: "#101114",
                    950: "#0a0b0d",
                },
            },
        },
    },
    plugins: [forms, animations],
};
