import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  server: {
    // Indispensable pour Docker : écouter sur toutes les interfaces
    host: '0.0.0.0',
    hmr: {
      // Indispensable pour que ton navigateur puisse se connecter au flux HMR
      host: 'localhost',
    },
    // S'assurer que le port correspond à celui déclaré dans ton docker-compose
    port: 5173,
    watch: {
      usePolling: true, // Parfois nécessaire si les changements de fichiers ne sont pas détectés sur Windows/WSL
    },
  },
  plugins: [
    laravel({
      input: 'resources/js/app.js',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
});
