/**
 * @file
 * Gulpfile
 *
 * @todo Add Linting for JS, TWIG, Drupal CS.
 */
var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var browserify = require('gulp-browserify');

gulp.task('sass', function () {
  return gulp
    .src('./css/sass/**/*.{scss,sass}')
    .pipe(sass({
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(gulp.dest('css'));
});

// gulp.task('scripts', function () {
//   return gulp
//     .src('./js/es6/**/*.js')
//     .pipe(browserify({
//       insertGlobals : true,
//     }))
//     .pipe(gulp.dest('./js'))
// });

gulp.task('watch', function () {
  gulp.watch('./css/sass/**/*.{scss,sass}', ['sass'])
  // gulp.watch('./js/**/*.js', ['scripts'])
});

gulp.task('default', ['watch']);
