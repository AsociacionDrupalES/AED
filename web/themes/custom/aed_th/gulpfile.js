'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
var sassGlob = require('gulp-sass-glob');

function buildStyles() {
  return gulp.src('./sass/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./css'));
}

exports.build = buildStyles;
exports.watch = function () {
  gulp.watch('./sass/**/*.scss', buildStyles);
};
