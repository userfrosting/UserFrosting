/* To build bundles...
    1. npm run build-bundle
    2. npm run bundle
    3. npm run bundle-cleanup

   To get bower packages
    1. npm run bower-install
   
   To clean assets retrieved by bower
    1. npm run bower-clean
*/

let gulp = require('gulp');
let del = require('del');

let plugins = require('gulp-load-plugins')();

let sprinkleDirectory = '../app/sprinkles';

// The Sprinkle load order from sprinkles.json
let sprinkles = ['core'].concat(require(`${sprinkleDirectory}/sprinkles.json`)['base']);

// The directory where the bundle task should look for the raw assets, as specified in bundle.config.json
let sourceDirectory = `${sprinkleDirectory}/*/assets/`;

// The directory where the bundle task should place compiled assets.  The names of assets in bundle.result.json
// will be specified relative to this path.
let destDirectory = '../public/assets/';

// name of the bundle file
let bundleFile = 'bundle.config.json';

// base bundle config file
let bundleConfigFile = `./${bundleFile}`;

// location of the bower.json file in the sprinkle directory
let bowerSourcePath = `${sprinkleDirectory}/*/bower.json`;

// destination directory for bower assets
let bowerDestDirectory = './assets/vendor';

// Deletes assets fetched by bower-install
gulp.task('bower-clean', () => {
    return del(`${sprinkleDirectory}/*/assets/vendor`, {force:true});
});


// Gulp task to install bower packages
gulp.task('bower-install', () => {
    return gulp.src(bowerSourcePath)
        .pipe(plugins.debug())
        .pipe(plugins.install({args: [`config.directory=${bowerDestDirectory}`]}))
        .pipe(plugins.notify({
            onLast: true,
            message: 'All bower packages installed successfully'
        }));
});

// Executes bundleing tasks according to bundle.config.json files in each Sprinkle, as per Sprinkle load order.
// Respects bundle collision rules.
gulp.task('bundle-build', () => {
    let fs = require('fs');
    let copy = require('recursive-copy');
    let merge = require('merge-array-object');
    let cleanup = (e) => {
        // Delete temporary directory if exists
        fs.rmdirSync("./temp");
        // Delete created bundle.config.json file
        fs.unlinkSync(bundleConfigFile);
        // Propagate error
        throw e;
    };
    let config = {
        bundle: {},
        copy: []
    };
    sprinkles.forEach((sprinkle) => {
        let location = `${sprinkleDirectory}/${sprinkle}/`;
        if (fs.existsSync(`${location}${bundleFile}`)) {
            let currentConfig = require(`${location}${bundleFile}`);
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
    fs.writeFileSync(bundleConfigFile, JSON.stringify(config));
});

// Execute gulp-bundle-assets
gulp.task('bundle', () => {
    gulp.src(bundleConfigFile)
        .pipe(plugins.bundleAssets({
            base: './temp'
        }))
        .pipe(plugins.bundleAssets.results({
            dest: './'
        }))
        .pipe(gulp.dest(destDirectory));
});

gulp.task('bundle-clean', () => {
    // Clean up temporary files
    del("./temp", {force:true});
    del(bundleConfigFile, {force:true});
});

gulp.task('copy', function () {
    // TODO: Uglify JS and Minify CSS
    sprinkles.forEach((sprinkle) => {
        gulp.src(`../app/sprinkles/${sprinkle}/assets/**/*`)
            .pipe(gulp.dest(destDirectory));
    });
});
