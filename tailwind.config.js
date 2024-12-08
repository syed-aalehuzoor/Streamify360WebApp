import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'primary': '#ffffff',
                'secondary': '#06a4a6',
                'secondary-alt':'#07777b',
                'accent': '#f4f3f9',
                'success': '#1cc88a',
                'danger': '#e74a3b',
                'warning': '#f6c23e',
            },
            spacing: {
                '64': '16rem',
            },
            fontFamily: {
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
