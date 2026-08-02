const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 | 混合资源管理
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 | Mix提供了一个干净、流畅的API，用于为您的Laravel应用程序定义一些Webpack构建步骤。
 | 默认情况下,我们正在编译应用程序的Sass文件的和捆绑所有的JS文件。
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
