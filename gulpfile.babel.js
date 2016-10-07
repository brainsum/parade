'use strict'
/**
 * @file
 * Gulpfile
 *
 * This Drupal module uses Gulp with Laravel Elixir wrapper,
 * which helps setting up the build process easier and more readable.
 * Also we are using the new ES2015 JavaScript syntax, because why not?
 *
 * @see https://laravel.com/docs/master/elixir
 */

import Elixir from 'laravel-elixir'

/**
 * Configuration
 */
Elixir.config.sourcemaps = false
Elixir.config.notifications = false
Elixir.config.assetsPath = 'assets'
Elixir.config.publicPath = '.'
Elixir.config.css.sass.folder = 'styles'
Elixir.config.js.folder = 'scripts'
Elixir.config.browserSync = {
    open: false,
    reloadOnRestart: true,
    notify: false
}

/**
 * Build
 */
Elixir(mix => {

    mix
        // Compile SASS/SCSS styles.
        .sass('parade.admin.scss')

        // Compile and bundle scripts with Rollup.
        // @see http://rollupjs.org/guide/
        .rollup('parade.js')

    // Live reload the browser on file updates.
    Elixir.isWatching() && mix.browserSync()
})