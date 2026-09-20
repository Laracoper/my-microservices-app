import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    base: '/',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // fonts: [
            //     bunny('Instrument Sans', {
            //         weights: [400, 500, 600],
            //     }),
            // ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // Заставляет Vite слушать все интерфейсы внутри Docker
        port: 5173,      // Внутренний порт Vite
        strictPort: true,
        hmr: {
            host: 'localhost', // Бракзер в Windows стучится сюда напрямую за обновлением кода
            port: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
            usePolling: true, // Включаем опрос файлов для стабильности WSL 2
        },
    },

});
