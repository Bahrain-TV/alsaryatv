import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/splash-screen.css',
                'resources/css/thank-you-screen.css',
            ],
            refresh: true,
        }),
    ],
});
