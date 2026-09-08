import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // 'Noto Sans Khmer' after Figtree: Figtree has no Khmer
                // glyphs at all, so without it the browser falls back to
                // whatever Khmer font (if any) the OS happens to have,
                // which looks inconsistent or shows as tofu boxes.
                sans: ['Figtree', 'Noto Sans Khmer', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
