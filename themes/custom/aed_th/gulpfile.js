'use strict';
// @todo when we create a new partial and gulp is watching. It will not take care about that file until gulp task get re-run.
var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
// neat includes bourbon.
var neat = require('node-neat').includePaths;
var sassGlob = require('gulp-sass-glob');

gulp.task('sass', function () {
  return gulp.src('./sass/**/*.scss')
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(sassGlob())
    .pipe(sass({includePaths: neat}))
    .pipe(sass().on('error', sass.logError))
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'));
});

// sass:watch
gulp.task('sw', function () {
  gulp.watch('./sass/**/*.scss', ['sass']);
});