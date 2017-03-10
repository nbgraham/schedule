/**
 * Gulp file.
 * 
 * Used to concatenate most files, to minimize loading times,
 * and used to watch files and automate cordova / ripple emulation flow.
 * 
 * Installation:
 *  $ npm install -g jshint
 *  $ npm install gulp-less gulp-jshint gulp-sequence gulp-copy gulp-concat gulp-clean-css --save
 * 
 * Usage:
 *  $ gulp
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */

"use strict";

//  
var gulp = require('gulp'),
    less = require('gulp-less'),
    lint = require('gulp-jshint'),
    run  = require('gulp-sequence'),
    copy = require('gulp-copy'),
    concat = require('gulp-concat'),
    cssMinify = require('gulp-clean-css')
;

/**
 * Check for dumb javascript.
 */
gulp.task('js-lint', function () {
    return gulp.src([
            'resources/scripts/**/*.js',
            '!resources/scripts/lib/**/*.js'
        ])
        .pipe(lint())
        .pipe(lint.reporter('default'))
    ;
});

/**
 * Compile the less files.
 */
gulp.task('build-less', function () {
    return gulp.src('resources/less/*.less')
        .pipe(less())
        .pipe(cssMinify())
        .pipe(gulp.dest('www/css'))
    ;
});

/**
 * Concatenate the CSS libraries.
 */
gulp.task('build-css-libs', function () {
    gulp.src(['resources/css/boot*.min.css'])
        .pipe(concat('bootstrap.min.css', {newLine: "\n\n"}))
        .pipe(gulp.dest('www/css'))
    ;

    gulp.src(['resources/css/jquery*.min.css'])
        .pipe(concat('jquery.min.css', {newLine: "\n\n"}))
        .pipe(gulp.dest('www/css'))
    ;

    gulp.src('./resources/css/font-awesome.min.css')
        .pipe(copy('www/css/.', {prefix: 3}))
    ;
});

/**
 * Watch for changes in the Less and JS files and issue cordova prepare as necessary.
 */
gulp.task('watch', function () {
    gulp.watch(__dirname + '/resources/less/*.less', ['build-less']);
    gulp.watch(__dirname + '/resources/scripts/**/*.js', ['build-js']);
});

/**
 * This task is automatically executed.
 */
gulp.task('default', function () {
    return run('build-less', 'build-css-libs', 'build-js-libs', 'js-lint', 'build-js',  'watch', function () {

    });
});

