import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

// Alias sem usar path ou __dirname
export default defineConfig({
  base: '/',
  plugins: [
    vue(),
    tailwindcss()
  ],
  resolve: {
    alias: {
      '@': '/src', // Vite resolve a partir do root
    },
  },
  server: {
    host: true,
    port: 3000
  },
  build: {
    target: 'esnext',
    outDir: 'dist'
  }
});
