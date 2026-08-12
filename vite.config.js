import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/pages/login.js',
                'resources/js/pages/home.js',
                'resources/js/pages/movimentacoes.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: '192.168.1.5',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
