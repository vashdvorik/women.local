import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Воркер PDF (pdf.worker.mjs) кладётся в сборку под расширением .js. Многие серверы (в том числе
                // хостинг на cPanel) отдают .mjs без типа JavaScript, и браузер отказывается его загружать:
                // «Failed to fetch dynamically imported module». Для .js тип у сервера всегда верный.
                assetFileNames: (asset) => {
                    const name = asset.names?.[0] ?? asset.name ?? '';

                    return name.endsWith('.mjs') ? 'assets/[name]-[hash].js' : 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
