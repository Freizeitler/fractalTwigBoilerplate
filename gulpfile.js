'use strict';

var gulp = require('gulp'),
  watch = require('gulp-watch'),
  sass = require('gulp-sass'),
  uglify = require('gulp-uglify'),
  pump = require('pump'),
  concat = require('gulp-concat');


// gulp-sass
gulp.task('sassPatterns', function () {
  return gulp.src('patterns/**/*.scss')
    .pipe(sass({
      outputStyle: 'compressed'
    }).on('error', sass.logError))
    .pipe(gulp.dest(function (file) {
      return file.base;
    }));
});
gulp.task('sassGlobals', function () {
  return gulp.src('scss/app.scss')
    .pipe(sass({
      outputStyle: 'compressed'
    }).on('error', sass.logError))
    .pipe(gulp.dest('assets/css/'));
});
gulp.task('sassIE', function () {
  return gulp.src('scss/ie.scss')
    .pipe(sass({
      outputStyle: 'compressed'
    }).on('error', sass.logError))
    .pipe(gulp.dest('assets/css/'));
});

// gulp-uglify
gulp.task('compressJs', function (cb) {
  pump([
      gulp.src('./assets/js/app.js'),
      uglify(),
      gulp.dest(function (file) {
        return file.base;
      })
    ],
    cb
  );
});

// gulp-concat
gulp.task('concatScripts', function() {
  return gulp.src('patterns/**/*.js')
    .pipe(concat('app.js'))
    .pipe(gulp.dest('./assets/js/'));
});

// gulp-watch
gulp.task('watch', function () {
  gulp.watch('patterns/**/*.scss', ['sassPatterns', 'copy']);
  gulp.watch('scss/*.scss', ['sassGlobals', 'sassIE', 'sassPatterns', 'copy']);
  gulp.watch('patterns/**/*.js', ['concatScripts', 'compressJs', 'copy']);
});

// gulp-copy
gulp.task('copy', ['sassPatterns', 'sassGlobals', 'concatScripts', 'compressJs'], function() {
  gulp.src(['./patterns/**/*']).pipe(gulp.dest('./dist/patterns'));
  gulp.src(['./assets/**/*']).pipe(gulp.dest('./dist/assets'));
  gulp.src(['./vendor/twig/**/*']).pipe(gulp.dest('./dist/vendor/twig'));
  gulp.src(['./vendor/symfony/**/*']).pipe(gulp.dest('./dist/vendor/symfony'));
  gulp.src(['./vendor/composer/**/*']).pipe(gulp.dest('./dist/vendor/composer'));
  gulp.src(['./vendor/autoload.php']).pipe(gulp.dest('./dist/vendor'));
  gulp.src(['./data/**/*']).pipe(gulp.dest('./dist/data'));
  gulp.src(['./*.php']).pipe(gulp.dest('./dist/'));
});

// default
gulp.task('default', ['sassPatterns', 'sassGlobals', 'concatScripts', 'compressJs', 'copy']);

