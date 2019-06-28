const mix = require('laravel-mix');
const webpack = require('webpack');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

var publicPath = mix.inProduction() ? 'public' : '../../../public/vendor/chatsupport';

mix.autoload({
    jquery: ['$', 'window.jQuery'],
    vue: ['Vue', 'window.Vue']
});

mix.setPublicPath(publicPath)
    .js('resources/js/app.js', publicPath)
    .extract(['vue', 'lodash', 'bootstrap'])
    .sass('resources/sass/app.scss', publicPath)
    .webpackConfig({
        resolve: {
            symlinks: false,
            alias: {
                '@': path.resolve(__dirname, 'resources/js/'),
            },
        }
    });

if (mix.inProduction()) {
    mix.options({
        uglify: {
            uglifyOptions: {
                compress: {
                    drop_console: true,
                },
            },
        },
    })
    .version();
}
