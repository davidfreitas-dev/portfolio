/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#01c38d',
        'primary-hover': '#6ec496',
        'primary-pressed': '#43a370',
        'primary-accent': '#edf8f2',
        secondary: '#888888',
        background: '#ffffff',
        accent: '#f1f1f1',
        font: '#3c3c3c',
      }
    },
  },
  plugins: [],
};

