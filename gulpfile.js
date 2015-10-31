var gulp       = require('gulp'),
    gulpif     = require('gulp-if'),
    less       = require('gulp-less'),
    concat     = require('gulp-concat'),
    minifyCSS  = require('gulp-minify-css'),
    uglify     = require('gulp-uglify'),
    watch      = require('gulp-watch'),
    rename     = require('gulp-rename'),
    imagemin   = require('gulp-imagemin'),
    argv       = require('yargs').argv,
    prefix     = require('gulp-autoprefixer'),
    connect    = require('gulp-connect'),
    shell      = require('gulp-shell'),
    rev        = require('gulp-rev');

// Options
var options = {
    target: argv.target || 'public',
    host: argv.host || 'localhost',
    port: argv.port || 8000,
    env: argv.env || 'local',
    livereload: argv.livereload || false
};

// Is a production build
var IS_PROD_BUILD = (options.env === 'production');

// Error catcher
function swallowError (err) {
    console.error(err);
    throw err;
}

// Compile Less and save to stylesheets directory
gulp.task('less', function () {

    var destDir = options.target + '/assets/stylesheets/',
        destFile = 'bootstrap.css';

    return gulp.src('source/assets/stylesheets/bootstrap.less')
        .pipe(less())
        .on('error', swallowError)
        .pipe(prefix('last 2 versions', '> 1%', 'Explorer 7', 'Android 2'))
        .pipe(gulpif(IS_PROD_BUILD, minifyCSS()))
        .pipe(rename(destFile))
        .pipe(gulp.dest(destDir));
});

// Publish Images
gulp.task('images', function () {
    if (IS_PROD_BUILD) {
        var destDir = options.target + '/assets/images';

        return gulp.src('source/assets/images/*')
            .pipe(imagemin({
                progressive: true,
                svgoPlugins: [{removeViewBox: false}]
            }))
            .pipe(gulp.dest(destDir));
    }
});

// Publish JavaScript
gulp.task('js', function () {

    var destDir = options.target + '/assets/javascripts/',
        destFile = 'app.js';

    return gulp.src([
            'source/assets/javascripts/bootstrap-modal.js',
            'source/assets/javascripts/bootstrap-dropdown.js',
            'source/assets/javascripts/bootstrap-scrollspy.js',
            'source/assets/javascripts/bootstrap-tabs.js',
            'source/assets/javascripts/bootstrap-twipsy.js',
            'source/assets/javascripts/bootstrap-popover.js',
            'source/assets/javascripts/default.js'
        ])
        .on('error', swallowError)
        .pipe(concat(destFile))
        .pipe(gulpif(IS_PROD_BUILD, uglify()))
        .pipe(gulp.dest(destDir));
});

// Webserver
gulp.task('webserver', function() {
    connect.server({
        host: options.host,
        port: options.port,
        livereload: options.livereload,
        root: options.target
    });
});

// Run skosh build command on pages
gulp.task('compile-pages', shell.task([
    'php skosh build --part=pages'
]));

// Run skosh build command on static content
gulp.task('compile-images', shell.task([
    'php skosh build --part=static'
]));

// What tasks does running gulp trigger?
gulp.task('default', ['build']);

gulp.task('serve', ['compile-pages', 'compile-images', 'webserver', 'watch']);

gulp.task('watch', ['build'], function() {
    gulp.watch('source/assets/stylesheets/**/*.less', ['less']);
    gulp.watch('source/assets/javascripts/**/*.js', ['js']);
    gulp.watch('source/assets/images/*', ['compile-images']);
    gulp.watch('source/**/*.{textile,twig,md}', ['compile-pages']);
});

gulp.task('build', ['images', 'less', 'js'], function () {
    // Create manifest of assets
    if (IS_PROD_BUILD) {
        return gulp.src(options.target + '/assets/**/*.{css,js,svg,png,gif,jpg,jpeg}')
            .pipe(gulp.dest(options.target + '/assets/'))
            .pipe(rev())
            .pipe(gulp.dest(options.target + '/assets/'))
            .pipe(rev.manifest())
            .pipe(gulp.dest('./'));
    }
});