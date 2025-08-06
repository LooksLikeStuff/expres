// vite.config.js

import path from 'path';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    vue(),
    laravel({
      input: [
        'resources/js/bootstrap.js',
        'resources/css/admin.css',
        'resources/css/style.css',
        'resources/css/font.css',
        'resources/css/element.css',
        'resources/css/animation.css',
        'resources/css/mobile.css',
        'resources/js/modal.js',
        'resources/js/success.js',
        'resources/js/login.js',
        'resources/js/mask.js',
        'resources/js/ratings.js',
        // Новая система загрузки файлов на Яндекс.Диск v3.0 находится в public/js
        'public/js/jquery-3.6.0.min.js',
        'public/js/wow.js',
        'public/css/animate.css'
      ],
      refresh: true,
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js')
    }
  },
  server: {
    hmr: {
      overlay: false
    },
    cors: {
      origin: ['http://back', 'https://back', 'http://127.0.0.1', 'http://localhost'],
      credentials: true
    }
  },
  define: {
    'process.env': {}
  }
});
