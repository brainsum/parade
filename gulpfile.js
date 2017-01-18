// @codingStandardsIgnoreFile
var gulp = require('gulp');
var sass = require('gulp-sass');
var csscomb = require('gulp-csscomb');
var eslint = require('gulp-eslint');
var autoprefixer = require('gulp-autoprefixer');
var merge = require('merge-stream');

var sassOptions = {
  outputStyle: 'expanded',
	includePaths: [
      process.cwd() + '/node_modules',
      process.cwd() + '/node_modules/susy/sass', 
	]
};

gulp.task('sass', function () {
  var parade = gulp
    .src('sass/**/*.{scss,sass}')
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(csscomb())
    .pipe(gulp.dest('css'));

  var parade_demo = gulp
    .src('modules/parade_demo/sass/**/*.{scss,sass}')
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(csscomb())
    .pipe(gulp.dest('modules/parade_demo/css'));

  return merge(parade, parade_demo);
});

gulp.task('csscomb', function () {
  var parade = gulp
    .src('css/**/*.css')
    .pipe(csscomb())
    .pipe(gulp.dest('css'));

  var parade_demo = gulp
    .src('modules/parade_demo/css/**/*.css')
    .pipe(csscomb())
    .pipe(gulp.dest('modules/parade_demo/css'));

  return merge(parade, parade_demo);
});

gulp.task('eslint', function () {
  var parade = gulp
    .src('js/**/*.js')
    .pipe(eslint())
    .pipe(eslint.format());

  var parade_demo = gulp
    .src('modules/parade_demo/js/**/*.js')
    .pipe(eslint())
    .pipe(eslint.format());

  return merge(parade, parade_demo);
});

gulp.task('copy:js', function () {
  return gulp
    .src([
      'node_modules/iphone-inline-video/dist/iphone-inline-video.browser.js',
      'node_modules/rellax/rellax.min.js',
    ])
    .pipe(gulp.dest('js/lib'));
});

gulp.task('copy', ['copy:js', 'copy:css']);
gulp.task('lint', ['csscomb', 'eslint']);

gulp.task('watch', ['lint'], function () {
  gulp.watch('**/sass/**/*.{scss,sass}', ['sass']);
  gulp.watch('**/js/**/*.js', ['eslint']);
});

gulp.task('compile', ['sass', 'lint']);
gulp.task('default', ['compile']);
