import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Bind to all network interfaces in the container
        port: 5173,
        hmr: {
            host: 'localhost', // Use 'localhost' or your host machine's IP
            protocol: 'ws',   // Use WebSocket protocol
        },
    },
});
