import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif', 'Figtree', ...defaultTheme.fontFamily.sans],
      },

      colors: {
        primary: {
          50: '#fef6e8',
          100: '#fee8c7',
          200: '#fdd19a',
          300: '#fcb25c',
          400: '#fba22e',
          500: '#f39200', // Couleur principale
          600: '#e08500',
          700: '#b86a00',
          800: '#8f5200',
          900: '#6e3f00',
          950: '#422500',
        },
        secondary: {
          50: '#e6f5ef',
          100: '#cce6db',
          200: '#99ccb6',
          300: '#66b392',
          400: '#33996d',
          500: '#006633', // Vert principal
          600: '#00572c',
          700: '#004723',
          800: '#00381b',
          900: '#002813',
          950: '#00190c',
        },
        gray: {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280', // text-gray-500
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
          950: '#030712',
        },
      },

      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '1.5rem',
        '3xl': '2rem',
      },
      boxShadow: {
        'soft': '0 2px 4px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)',
        'medium': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03)',
      }
    },
  },

  plugins: [forms],
};
