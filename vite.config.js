import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
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
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Sales App',
                short_name: 'Sales',
                start_url: '/',
                display: 'standalone',
                background_color: '#ffffff',
                theme_color: '#2563eb',
                icons: [
                    {
                        src: '/apple-touch-icon.png',
                        sizes: '180x180',
                        type: 'image/png'
                    },
                    {
                        src: '/favicon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/favicon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,png,svg,ico}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/(localhost:8000|127\.0\.0\.1:8000|your-api-domain\.com)\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            networkTimeoutSeconds: 10,
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 24 * 60 * 60 // 1 day
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    }
                ]
            }
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    optimizeDeps: {
        include: ['@inertiajs/vue3'],
    },
}); 