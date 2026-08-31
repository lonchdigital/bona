import { resolve } from 'path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';

export default defineConfig(({ mode }) => ({
    define: {
        'process.env.NODE_ENV': JSON.stringify(mode),
    },
    server: {
        host: 'bona.local',
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 4 still uses Sass' legacy module API internally.
                // Keep CI signal useful until the separate Bootstrap 5 UI migration.
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function', 'abs-percent'],
            },
        },
    },
    build: {
        rollupOptions: {
            output: {
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
    resolve: {
        alias: {
            $fonts: resolve('./resources/fonts'),
            $img: resolve('./resources/img'),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/admin/app.js',
                'resources/js/admin/scripts.js',
                'resources/js/admin/date-picker.js',
                'resources/scss/libs.scss',
                'resources/scss/theme-additional.scss',
                'resources/js/store/app.js',
                'bona-html/img/interior-bedroom.jpg',
                'bona-html/img/interior-living.jpg',
                'bona-html/img/interior-hall.jpg',
                'bona-html/img/interior-apartment.jpg',
                'bona-html/img/interior-house.jpg',
                'bona-html/img/interior-office.jpg',
                'bona-html/eVidnovlennya.svg',
                'bona-html/monobank-logo.svg',
                'bona-html/privatbank-chastyny.svg',
                'bona-html/img/manager-oksana.webp',
            ],
            refresh: true,
        }),
        vue(),
        i18n(),
    ],
}));
