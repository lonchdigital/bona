import { resolve } from 'path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// import { svgSprite } from 'rollup-plugin-svgsprite-generator';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';

export default defineConfig(({ mode }) => {
    return {
        define: {
            'process.env.NODE_ENV': JSON.stringify(mode)
        },
        server: {
            host: 'bona.local'
        },
        build: {
            manifest: true,
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
                $img: resolve('./resources/img')
            }
        },
        plugins: [

            laravel({
                input: [
                    'resources/js/admin/app.js',
                    'resources/js/admin/scripts.js',

                    'resources/scss/libs.scss',
                    'resources/scss/main.scss',
                    'resources/scss/theme-additional.scss',

                    'resources/js/store/app.js'
                    // 'resources/js/store/homepage.js'

                    // 'resources/js/store/all.common.files.js',
                    // 'resources/js/store/pages/store.home.js'
                ],
                refresh: true
            }),


            vue(),
            i18n()
        ],

    }

});
