/* To build bundles...
    1. npm run uf-bundle-build
    2. npm run uf-bundle
    3. npm run uf-bundle-clean

   To get frontend vendor packages via bower
    1. npm run uf-assets-install

   To clean frontend vendor assets
    1. npm run uf-assets-clean
*/
"use strict";
const gulp = require('gulp');
const del = require('del');
const fs = require('fs');
const shell = require('shelljs');
const plugins = require('gulp-load-plugins')();

const sprinklesDir = '../app/sprinkles/';

const sprinklesSchemaPath = '../app/sprinkles.json';

// The Sprinkle load order from sprinkles.json
const sprinkles = ['core'].concat(require(`${sprinklesSchemaPath}`)['base']);

// The directory where the bundle task should place compiled assets.
// The names of assets in bundle.result.json will be specified relative to this path.
const publicAssetsDir = '../public/assets/';

// name of the bundle file
const bundleFile = 'asset-bundles.json';

// Compiled bundle config file
const bundleConfigFile = `./${bundleFile}`;

// Destination directory for vendor assets
const vendorAssetsDir = './assets/vendor';

/**
 * Vendor asset task
 */

// Gulp task to install vendor packages via bower
gulp.task('bower-install', () => {
    "use strict";
    shell.cd(`${sprinklesDir}`);
    sprinkles.forEach((sprinkle) => {
        if (fs.existsSync(`${sprinkle}/bower.json`)) {
            console.log(`bower.json found in '${sprinkle}' Sprinkle, installing assets.`);
            shell.cd(`${sprinkle}`);
            shell.exec(`bower install --config.directory=${vendorAssetsDir}`);
            shell.cd(`../`);
        }
    });

    return true;
});

/**
 * Bundling tasks
 */

// Executes bundleing tasks according to bundle.config.json files in each Sprinkle, as per Sprinkle load order.
// Respects bundle collision rules.
gulp.task('bundle-build', () => {
    "use strict";
    let copy = require('recursive-copy');
    let merge = require('merge-array-object');
    let cleanup = (e) => {
        "use strict";
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
        "use strict";
        let location = `${sprinklesDir}/${sprinkle}/`;
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
                            config.bundle[bundleName] = merge(currentConfig.bundle[bundleName], config.bundle[bundleName]);
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

    // Copy all assets in order of Sprinkle load order.
    let sprinkleAssets = []
    sprinkles.forEach((sprinkle) => {
        "use strict";
        sprinkleAssets.push(`../app/sprinkles/${sprinkle}/assets/**/*`);
    });
    // Gulp v4 src respects order, until it is released, use this alternative.
    return plugins.srcOrderedGlobs(sprinkleAssets)
            .pipe(plugins.copy('../public/assets/', {prefix: 5}));// And gulp.dest doesn't give us the control needed.
});

// Execute gulp-bundle-assets
gulp.task('bundle', () => {
    "use strict";
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
    "use strict";
    return del(publicAssetsDir, { force: true });
});

// Clean up temporary bundling files
gulp.task('bundle-clean', () => {
    "use strict";
    return del(bundleConfigFile, { force: true });
});

// Deletes assets fetched by bower-install
gulp.task('bower-clean', () => {
    "use strict";
    return del(`${sprinklesDir}/*/assets/vendor`, { force: true });
});

// Deletes all generated, or acquired files.
gulp.task('clean', () => {
    "use strict";
    del(publicAssetsDir, { force: true});
    del(bundleConfigFile, { force: true });
    del(`${sprinklesDir}/*/assets/vendor`, { force: true });

    return true;
})