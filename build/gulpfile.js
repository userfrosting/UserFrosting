'use strict';

// Load environment variables
require('dotenv').config({path: '../app/.env'});

/**
 * Global dependencies
 */
const gulp = require('gulp');
const fs = require('fs-extra');
const del = require('del');
const plugins = require('gulp-load-plugins')();

// Set up logging
let doILog = (process.env.UF_MODE == 'dev');
let logger = (message) => {
    if (doILog) {
        console.log(message);
    }
};

const sprinklesDir = '../app/sprinkles';
const sprinklesSchemaPath = '../app/sprinkles.json';

// The Sprinkle load order from sprinkles.json
const sprinkles = require(`${sprinklesSchemaPath}`)['base'];

// The directory where the bundle task should place compiled assets. 
// The names of assets in bundle.result.json will be located relative to this path.
const publicAssetsDir = '../public/assets/';

// name of the bundle file
const sprinkleBundleFile = 'asset-bundles.json';

// Merged bundle config file with relative dir
const bundleConfigFile = './bundle.config.json';

/**
 * Vendor asset task
 */
gulp.task('assets-install', () => {
    'use strict';

    // Legacy clean up
    let legacyVendorAssets = '../app/sprinkles/*/assets/vendor/**';
    if (del.sync(legacyVendorAssets, { dryRun: true, force: true }).length > 0) {
        logger('Frontend vendor assets are now located at "app/assets".\nStarting clean up of legacy vendor assets...');
        del.sync(legacyVendorAssets, { force: true });
        logger('Complete.')
    }

    let mergePkg = require('@userfrosting/merge-package-dependencies');

    // See if there are any yarn packages.
    let yarnPaths = [];
    for (let sprinkle of sprinkles) {
        if (fs.existsSync(`../app/sprinkles/${sprinkle}/package.json`)) {
            yarnPaths.push(`../app/sprinkles/${sprinkle}/package.json`);
        }
    }
    if (yarnPaths.length > 0) {
        // Yes there are!

        // Delete old package.json and yarn.lock
        del.sync(['../app/assets/package.json', '../app/assets/yarn.lock'], { force: true });

        // Generate package.json
        let yarnTemplate = {// Private makes sure it isn't published, and cuts out a lot of unnecessary fields.
            private: true
        };
        logger('\nMerging packages...\n');
        mergePkg.yarn(yarnTemplate, yarnPaths, '../app/assets/', doILog);
        logger('\nMerge complete.\n');

        // Yarn automatically removes extraneous packages.

        // Perform installation.
        // --flat switch cannot be used due to spotty support of --non-interactive switch
        // Thankfully, "resolutions" works outside flat mode.
        logger('Installing npm/yarn assets...');
        require('child_process').execSync('yarn install --non-interactive', {
            cwd: '../app/assets',
            preferLocal: true,// Local over PATH.
            localDir: './node_modules/.bin',
            stdio: doILog ? 'inherit' : ''
        });

        // Ensure dependency tree is flat manually because Yarn errors out with a TTY error.
        logger('\nInspecting dependency tree...\n')

        if (!mergePkg.yarnIsFlat('../app/assets/', doILog)) {
            logger(`
Dependency tree is not flat! Dependencies must be flat to prevent abnormal behavior.
Recommended solution is to adjust dependency versions until issue is resolved to ensure 100% compatibility.
Alternatively, resolutions can be used as an override, as documented at https://yarnpkg.com/en/docs/selective-version-resolutions
`);
            throw 'Dependency tree is not flat!';
        } else {
            logger('\nDependency tree is flat and usable.\n')
        }
    }
    else del.sync([
        '../app/assets/package.json',
        '../app/assets/node_modules/',
        '../app/assets/yarn.lock'
    ], { force: true });

    // See if there are any bower packages.
    let bowerPaths = [];
    for (let sprinkle of sprinkles) {
        // bower
        if (fs.existsSync(`../app/sprinkles/${sprinkle}/bower.json`)) {
            console.warn(`DEPRECATED: Detected bower.json in ${sprinkle} Sprinkle. Support for bower (bower.json) will be removed in the future, please use npm/yarn (package.json) instead.`);
            bowerPaths.push(`../app/sprinkles/${sprinkle}/bower.json`);
        }
    }
    if (bowerPaths.length > 0) {
        // Yes there are!

        // Delete old bower.json
        del.sync('../app/assets/bower.json', { force: true });

        // Generate bower.json
        let bowerTemplate = {
            name: 'uf-vendor-assets'
        };
        logger('\nMerging packages...\n');
        mergePkg.bower(bowerTemplate, bowerPaths, '../app/assets/', doILog);
        logger('\nMerge complete.\n');

        let childProcess = require('child_process');

        // Remove extraneous packages
        childProcess.execSync('bower prune', {
            cwd: '../app/assets',
            preferLocal: true,// Local over PATH.
            localDir: './node_modules/.bin',
            stdio: doILog ? 'inherit' : ''
        });

        // Perform installation
        childProcess.execSync('bower install -q --allow-root', { // --allow-root stops bower from complaining about being in 'sudo'.
            cwd: '../app/assets',
            preferLocal: true,// Local over PATH.
            localDir: './node_modules/.bin',
            stdio: doILog ? 'inherit' : ''
        });
        // Yarn is able to output its completion. Bower... not so much.
        logger('Done.\n');
    }
    else del.sync([
        '../app/assets/bower.json',
        '../app/assets/bower_components/'
    ], { force: true });
});


/**
 * Bundling tasks
 */

// Executes bundling tasks according to bundle.config.json files in each Sprinkle, as per Sprinkle load order.
// Respects bundle collision rules.
gulp.task('bundle-build', () => {
    'use strict';
    let copy = require('recursive-copy');
    let merge = require('merge-array-object');
    let cleanup = (e) => {
        'use strict';
        // Delete temporary directory if exists
        fs.rmdirSync('./temp');
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
        'use strict';
        let location = `${sprinklesDir}/${sprinkle}/`;
        if (fs.existsSync(`${location}${sprinkleBundleFile}`)) {
            // Require shouldn't be used here.
            let currentConfig = require(`${location}${sprinkleBundleFile}`);
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
    });
    // Save bundle rules to bundle.config.json
    fs.writeFileSync(bundleConfigFile, JSON.stringify(config));

    // Copy vendor assets (bower, then npm)
    /** @todo Should really keep the garbage files out. A filter function can be passed to the copySync settings object. */
    let paths = [
        '../app/assets/bower_components/',
        '../app/assets/node_modules/'
    ];
    for (let path of paths) {
        if (fs.pathExistsSync(path)) fs.copySync(path, `${publicAssetsDir}vendor/`, { overwrite: true });
    }
    // Copy sprinkle assets
    paths = [];
    for (let sprinkle of sprinkles) {
        let path = `../app/sprinkles/${sprinkle}/assets/`;
        if (fs.pathExistsSync(path)) fs.copySync(path, publicAssetsDir, { overwrite: true });
    }
    return;
});

// Execute gulp-bundle-assets
gulp.task('bundle', () => {
    'use strict';
    return gulp.src(bundleConfigFile)
        .pipe(plugins.ufBundleAssets({
            base: publicAssetsDir
        }))
        .pipe(plugins.ufBundleAssets.results({
            dest: './'
        }))
        .pipe(gulp.dest(publicAssetsDir));
});



/**
 * Clean up tasks
 */

gulp.task('public-clean', () => {
    'use strict';
    return del(publicAssetsDir, { force: true });
});

// Clean up temporary bundling files
gulp.task('bundle-clean', () => {
    'use strict';
    return del(bundleConfigFile, { force: true });
});

// Deletes assets fetched by assets-install
gulp.task('assets-clean', () => {
    'use strict';
    return del(['../app/assets/bower_components/', '../app/assets/node_modules/', '../app/assets/bower.json', '../app/assets/package.json'], { force: true });
});

// Deletes all generated, or acquired files.
gulp.task('clean', ['public-clean', 'bundle-clean', 'assets-clean'], () => { });