import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        proxy: {
            // Livewire endpoints
            '^/livewire/update':      { target: 'http://localhost', changeOrigin: true },
            '^/livewire/upload-file': { target: 'http://localhost', changeOrigin: true },
            '^/livewire/preview-file':{ target: 'http://localhost', changeOrigin: true },

            // Echo / Broadcasting auth (for Reverb/Pusher)
            '^/broadcasting/auth':    { target: 'http://localhost', changeOrigin: true },
            '^/app/.*': { target: 'ws://localhost:8080', changeOrigin: true, ws: true },
        },
    },
})
