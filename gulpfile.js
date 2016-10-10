/**
 * @file
 * Gulpfile for automatizing some tasks.
 *
 * @todo Add Linting for JS, TWIG, Drupal CS.
 */

var gulp = require('gulp');
var sass = require('gulp-sass');
var csscomb = require('gulp-csscomb');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('sass', function () {
  return gulp
    .src('./css/sass/**/*.{scss,sass}')
    .pipe(sass({
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(csscomb())
    .pipe(gulp.dest('css'));
});

gulp.task('watch', function () {
  gulp.watch('./css/sass/**/*.{scss,sass}', ['sass']);
});

gulp.task('default', ['watch']);
