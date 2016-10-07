/**
 * @file
 * Gulpfile
 *
 * @todo Add Linting for JS, TWIG, Drupal CS.
 */
var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('sass', function () {
  return gulp
    .src('./css/sass/**/*.{scss,sass}')
    .pipe(sass({
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(gulp.dest('css'));

});

gulp.task('default', ['watch']);

gulp.task('watch', function () {
  gulp.watch('./css/sass/**/*.{scss,sass}', ['sass'])
});
