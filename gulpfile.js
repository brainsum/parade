/**
 * @file
 * Gulpfile for automatizing some tasks.
 *
 * @todo Add Linting for JS, TWIG, Drupal CS.
 */

var gulp = require('gulp');
var sass = require('gulp-sass');
var csscomb = require('gulp-csscomb');
var eslint = require('gulp-eslint');
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

gulp.task('lint:css', function () {
  return gulp
    .src('./css/*.css')
    .pipe(csscomb());
});

gulp.task('lint:js', function () {
  return gulp
    .src('./js/**/*.js')
    .pipe(eslint())
    .pipe(eslint.format());
});

gulp.task('lint', ['lint:css', 'lint:js']);

gulp.task('watch', function () {
  gulp.watch('./css/sass/**/*.{scss,sass}', ['sass']);
});

gulp.task('default', ['watch']);
