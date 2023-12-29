const { svgSprite } = require("rollup-plugin-svgsprite-generator");

console.log('Generating icon bundle');

svgSprite({
    input: './resources/icons/',
    output: './resources/img/icon.svg',
    minify: false,
    doctype: false
}).generateBundle();

console.log('Success!\n');

