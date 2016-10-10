// @codingStandardsIgnoreFile
var gulp = require('gulp');
var sass = require('gulp-sass');
var csscomb = require('gulp-csscomb');
var eslint = require('gulp-eslint');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('sass', function () {
  return gulp
    .src('sass/**/*.{scss,sass}')
    .pipe(sass({
      outputStyle: 'expanded'
    }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(csscomb())
    .pipe(gulp.dest('css'));
});

gulp.task('csscomb', function () {
  return gulp
    .src('css/**/*.css')
    .pipe(csscomb())
    .pipe(gulp.dest('css'));
});

gulp.task('eslint', function () {
  return gulp
    .src('js/**/*.js')
    .pipe(eslint())
    .pipe(eslint.format());
});

gulp.task('lint', ['csscomb', 'eslint']);

gulp.task('watch', ['lint'], function () {
  gulp.watch('sass/**/*.{scss,sass}', ['sass']);
  gulp.watch('js/**/*.js', ['eslint']);
});

gulp.task('default', ['watch']);
