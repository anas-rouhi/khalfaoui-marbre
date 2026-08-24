import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
                // Cairo couvre l'arabe ; Inter reste en secours pour les
                // chiffres et les mots latins intercalés.
                arabic: ['Cairo', 'Inter', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // Vert émeraude repris du monogramme "KM" du logo.
                brand: {
                    50: '#eefbf3',
                    100: '#d6f5e2',
                    200: '#b0e9ca',
                    300: '#7bd7ab',
                    400: '#43bd88',
                    500: '#1e9d6b',
                    600: '#0f7d55',
                    700: '#0c6446',
                    800: '#0b4f39',
                    900: '#0a4130',
                    950: '#04251b',
                },
                // Fonds sombres "obsidienne" façon pierre polie.
                obsidian: {
                    50: '#f6f6f7',
                    100: '#e2e3e5',
                    200: '#c5c7cb',
                    300: '#9fa2a9',
                    400: '#787c85',
                    500: '#5c6069',
                    600: '#484b53',
                    700: '#33363c',
                    800: '#212328',
                    900: '#141519',
                    950: '#0a0b0d',
                },
            },

            boxShadow: {
                luxe: '0 30px 60px -20px rgba(0, 0, 0, 0.55)',
                'brand-glow': '0 0 0 1px rgba(30, 157, 107, 0.35), 0 18px 40px -18px rgba(30, 157, 107, 0.55)',
            },

            backgroundImage: {
                'brand-sheen': 'linear-gradient(110deg, transparent 25%, rgba(255,255,255,0.16) 50%, transparent 75%)',
            },

            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(28px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                // Panoramique lent de l'image de fond du hero.
                'slow-pan': {
                    '0%': { transform: 'scale(1.12) translate3d(0, 0, 0)' },
                    '50%': { transform: 'scale(1.18) translate3d(-2.5%, -1.5%, 0)' },
                    '100%': { transform: 'scale(1.12) translate3d(0, 0, 0)' },
                },
                'sheen': {
                    '0%': { transform: 'translateX(-120%)' },
                    '100%': { transform: 'translateX(120%)' },
                },
                'scroll-hint': {
                    '0%, 100%': { opacity: '0.25', transform: 'translateY(0)' },
                    '50%': { opacity: '1', transform: 'translateY(8px)' },
                },
            },

            animation: {
                'fade-up': 'fade-up 0.9s cubic-bezier(0.22, 1, 0.36, 1) both',
                'fade-in': 'fade-in 1.2s ease-out both',
                'slow-pan': 'slow-pan 32s ease-in-out infinite',
                sheen: 'sheen 1.1s ease-out',
                'scroll-hint': 'scroll-hint 2.2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
