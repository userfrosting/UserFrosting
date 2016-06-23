var gulp = require('gulp');
var gulpLoadPlugins = require('gulp-load-plugins');
var plugins = gulpLoadPlugins();

// The directory where the bundle task should look for the raw assets, as specified in bundle.config.json
var sourceDirectory = '../app/assets/';

// The directory where the bundle task should place compiled assets.  The names of assets in bundle.result.json
// will be specified relative to this path.
var destDirectory = '../public/assets/';

gulp.task('build', function() {
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
