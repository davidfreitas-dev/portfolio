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
        secondary: '#888888',
        background: '#ffffff',
        accent: '#f1f1f1',
        font: '#3c3c3c',
      },
      width: {
        'sidebar-width': '250px',
      },
    },
  },
  plugins: [],
};

