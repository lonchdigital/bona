import { resolve } from 'path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svgSprite } from 'rollup-plugin-svgsprite-generator';
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
            minify: true,
            scriptLoad: 'defer'
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
                    // admin
                    'resources/scss/libs.scss',
                    'resources/scss/main.scss',
                    'resources/scss/theme-additional.scss',
                    'resources/js/admin/app.js',
                    'resources/js/admin/scripts.js',

                    // store
                    'resources/js/store/app.js'
                    // 'resources/css/styles.js'
                ]
            }),
            vue(),
            i18n()
        ]
    }

});
