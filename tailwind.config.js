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
    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Tajawal', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand Colors - CORRECTED from Al-Sarya Logo
                brand: {
                    maroon: '#A81C2E',      // Deep burgundy red (primary)
                    cream: '#E8D7C3',       // Soft cream/beige (secondary)
                    bronze: '#8B6914',      // Dark bronze/brass
                    'deep-red': '#7A1422',  // Darker maroon for hover
                    'light-cream': '#F5DEB3', // Light cream variant
                    'dark-brown': '#5C4033', // Dark brown for accents
                },
                // Custom dark backgrounds
                dark: {
                    'navy': '#0F172A',      // Main dark background
                    'slate': '#1E293B',     // Secondary dark background
                    'card': 'rgba(30, 41, 59, 0.6)', // Card background
                },
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #A81C2E, #E8D7C3)',
                'brand-gradient-reverse': 'linear-gradient(135deg, #E8D7C3, #A81C2E)',
                'brand-accent': 'linear-gradient(135deg, #E8D7C3, #8B6914)',
            },
        },
    },

    plugins: [forms, typography],
};
