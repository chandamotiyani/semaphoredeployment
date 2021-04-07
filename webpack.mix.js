let mix = require('laravel-mix');
mix.config.fileLoaderDirs.fonts = 'web/assets/fonts';

/*
require('@ayctor/laravel-mix-svg-sprite');

// Or customizable
mix.svgSprite({
    src: 'assets/icons/*.svg',
    filename: 'web/assets/icons/main.svg',
    prefix: ''
});
    //.svgSprite('assets/icons/', 'web/assets/icons/main.svg')
*/

/**
 * Images
 */
let ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;

mix.webpackConfig( {
    plugins: [
        new ImageminPlugin( {
            disable: false, //process.env.NODE_ENV !== 'production',
            pngquant: {
                quality: '95-100',
            },
            test: /\.(jpe?g|png|gif|svg)$/i,
        } ),
    ],
}).copy( 'assets/img', 'web/assets/img', false );

mix.copyDirectory('assets/fonts', 'web/assets/fonts');

mix
    .js('assets/js/main.js', 'web/assets/js/main.js')
    .sass('assets/scss/main.scss', 'web/assets/css/main.css')
    .sourceMaps()
    .options({
      processCssUrls: false,
      autoprefixer: {
        options: {
          grid: 'autoplace'
        }
      }
    })
    .setPublicPath('web');