var gulp = require('gulp');
var gulpLoadPlugins = require('gulp-load-plugins');
var plugins = gulpLoadPlugins();

// The directory where the bundle task should look for the raw assets, as specified in bundle.config.json
var sourceDirectory = '../app/sprinkles/*/assets/';
//['',
// The directory where the bundle task should place compiled assets.  The names of assets in bundle.result.json
// will be specified relative to this path.
var destDirectory = '../public/assets/';

gulp.task('build', ['copy'], function() {
    fb = gulp.src('./bundle.config.json')
        .pipe(plugins.bundleAssets({
            base: sourceDirectory
        }))
        .pipe(plugins.bundleAssets.results({
            dest: './'  // destination of bundle.result.json
        }))
        .pipe(gulp.dest(destDirectory));
    return fb;
});

gulp.task('copy', function () {
    // Copy images from core.  Obviously, we will need some way to properly iterate through the sprinkles directory, and override assets as necessary.
    gulp.src('../app/sprinkles/core/assets/images/**/*')
    .pipe(gulp.dest(destDirectory + 'images/'));

    // Copy azmind images
    gulp.src('../app/sprinkles/account/assets/vendor/azmind/images/**/*')
    .pipe(gulp.dest(destDirectory + 'vendor/azmind/images/'));

    // Copy favicons from core
    gulp.src('../app/sprinkles/core/assets/favicons/*')
    .pipe(gulp.dest(destDirectory + 'favicons/'));

    // Copy font-awesome font files from core.  Obviously we will want to find all sprinkles automatically, rather than having to explicitly define them here.
    gulp.src('../app/sprinkles/core/assets/vendor/font-awesome-4.5.0/fonts/**/*')
    .pipe(gulp.dest(destDirectory + 'fonts/'));

    // Copy font-starcraft font files from core.
    gulp.src('../app/sprinkles/core/assets/vendor/font-starcraft/fonts/**/*')
    .pipe(gulp.dest(destDirectory + 'fonts/'));
});
