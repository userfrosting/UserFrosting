/* To build bundles...
    1. gulp build-bundle
    2. gulp bundle
    3. gulp bundle-cleanup
*/

let gulp = require('gulp');
let plugins = require('gulp-load-plugins')();

// The Sprinkle load order from sprinkles.json
let sprinkles = ['core'].concat(require('../app/sprinkles/sprinkles.json'));

// The directory where the bundle task should look for the raw assets, as specified in bundle.config.json
let sourceDirectory = '../app/sprinkles/*/assets/';

// The directory where the bundle task should place compiled assets.  The names of assets in bundle.result.json
// will be specified relative to this path.
let destDirectory = '../public/assets/';

gulp.task('build', ['copy'], function () {
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

// Executes bundleing tasks according to bundle.config.json files in each Sprinkle, as per Sprinkle load order.
// Respects bundle collision rules.
gulp.task('build-bundle', () => {
    let fs = require('fs');
    let copy = require('recursive-copy');
    let merge = require('merge-array-object');
    let cleanup = (e) => {
        // Delete temporary directory if exists
        fs.rmdirSync("./temp");
        // Delete created bundle.config.json file
        fs.unlinkSync("./bundle.config.json");
        // Propagate error
        throw e;
    };
    let config = {
        bundle: {},
        copy: []
    };
    sprinkles.forEach((sprinkle) => {
        let location = `../app/sprinkles/${sprinkle}/`;
        if (fs.existsSync(`${location}bundle.config.json`)) {
            let currentConfig = require(`${location}bundle.config.json`);
            // Add bundles to config, respecting collision rules.
            for (let bundleName in currentConfig.bundle) {
                // If bundle already defined, handle as per collision rules.
                if (bundleName in config.bundle) {
                    let onCollision = 'replace';
                    try {
                        onCollision = (typeof currentConfig.bundle[bundleName].options.sprinkle.onCollision !== 'undefined' ? currentConfig.bundle[bundleName].options.sprinkle.onCollision : 'replace');
                    }
                    catch (e) {

                    }
                    switch (onCollision) {
                        case 'replace':
                            config.bundle[bundleName] = currentConfig.bundle[bundleName];
                            break;
                        case 'merge':
                            // If using this collision rule, keep in mind any bundling options will also be merged.
                            // Inspect the produced 'bundle.config.json' file in the 'build' folder to ensure options are correct.
                            config.bundle[bundleName] = merge(config.bundle[bundleName], currentConfig.bundle[bundleName]);
                            break;
                        case 'ignore':
                            // Do nothing. This simply exists to prevent falling through to error catchment.
                            break;
                        case 'error':
                            cleanup(`The bundle '${bundleName}' in the Sprinkle '${sprinkle}' has been previously defined, and the bundle's 'onCollision' property is set to 'error'.`);
                        default:
                            cleanup(`Unexpected input '${onCollision}' for 'onCollision' for the bundle '${bundleName}' in the Sprinkle '${sprinkle}'.`);
                    }
                }
                // Otherwise, just add.
                else {
                    config.bundle[bundleName] = currentConfig.bundle[bundleName];
                }
            }
            // Add/merge copy files to config
            if ('copy' in currentConfig) {
                config.copy = new Set(config.copy, currentConfig.copy);
            }
        }
        // Copy sprinkle assets to temporary directory, overwriting on conflict.
        if (fs.existsSync(`${location}assets/`)) {
            copy(location + "assets/", "./temp/", { overwrite: true });
        }
    });
    // Save bundle rules to bundle.config.json
    fs.writeFileSync('./bundle.config.json', JSON.stringify(config));
});

// Execute gulp-bundle-assets
gulp.task('bundle', () => {
    gulp.src('./bundle.config.json')
        .pipe(plugins.bundleAssets({
            base: './temp'
        }))
        .pipe(plugins.bundleAssets.results({
            dest: './'
        }))
        .pipe(gulp.dest(destDirectory));
});

gulp.task('bundle-cleanup', () => {
    let rimraf = require('rimraf');
    // Clean up temporary files
    rimraf("./temp", () => {

    });
    rimraf("./bundle.config.json", () => {
        
    });
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
