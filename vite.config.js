import { resolve } from 'path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svgSprite } from 'rollup-plugin-svgsprite-generator';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
import fs from 'fs';

export default defineConfig(({ mode }) => {
    return {
        define: {
            'process.env.NODE_ENV': JSON.stringify(mode)
        },
        server: {
            host: 'bona.local'
        },
        build: {
            minify: true
        },
        resolve: {
            alias: {
                $fonts: resolve('./resources/fonts'),
                $img: resolve('./resources/img')
            }
        },
        plugins: [
            {
                name: 'build-index',
                async buildStart(options) {
                    const filePath = './resources/img/icon.svg';

                    fs.access(filePath, fs.constants.F_OK, (err) => {
                        if (err) {
                            fs.writeFile(filePath, '', (err) => {
                                if (err) {
                                    console.error('Error while creating dummy icon file!');
                                    return;
                                }
                                console.log('Dummy icon file has been created!');
                            });
                        } else {
                            console.log('Icon file exists.');
                        }
                    });
                },
            },
            svgSprite({
                input: './resources/icons/',
                output: './resources/img/icon.svg',
                minify: false,
                doctype: false
            }),
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
