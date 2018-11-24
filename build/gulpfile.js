"use strict";
const { config: envConfig } = require("dotenv");
const { task, src, dest, parallel, series } = require("gulp");
const { readFileSync, existsSync, writeFileSync } = require("fs");
const { bower: mergeBowerDeps, yarn: mergeYarnDeps, yarnIsFlat } = require("@userfrosting/merge-package-dependencies");
const { sync: deleteSync } = require("del");
const { execSync } = require("child_process");
const concatJs = require("gulp-concat");
const { default: minifyJs } = require("gulp-uglify-es");
const concatCss = require("gulp-concat-css");
const minifyCss = require("gulp-clean-css");
const { ValidateRawConfig, MergeRawConfigs, default: Bundler } = require("gulp-uf-bundle-assets");
const rev = require("gulp-rev");
const prune = require("gulp-prune");
const { resolve: resolvePath } = require("path");

// Load environment variables
envConfig({ path: "../app/.env" });

// Set up logging
const doILog = (process.env.UF_MODE === "dev");

/**
 * Prints to stdout with newline when UF_MODE is dev.
 * @param {any} message Message to log.
 */
function Logger(message) {
    if (doILog) console.log(message);
}

// Path constants
const rootDir = "../";
const sprinklesDir = rootDir + "app/sprinkles/";
const sprinklesSchemaPath = rootDir + "app/sprinkles.json";
const publicAssetsDir = rootDir + "public/assets/";
const legacyVendorAssetsGlob = rootDir + "sprinkles/*/assets/vendor/**";
const sprinkleBundleFile = "asset-bundles.json";
const vendorAssetsDir = rootDir + "app/assets/";

// Load sprinkles
let sprinkles;
try {
    sprinkles = JSON.parse(readFileSync(sprinklesSchemaPath)).base;
}
catch (error) {
    Logger(sprinklesSchemaPath + " could not be loaded, does it exist?");
    throw error;
}

/**
 * Installs vendor assets. Mapped to npm script "uf-assets-install".
 */
task("assets-install", done => {
    try {
        // This script requires the npm environment, and therefore cannot be run directly with the gulp CLI.
        if (!process.env.npm_lifecycle_event) throw new Error("Assets installation must be run via 'npm run uf-assets-install'");

        // Clean up any legacy assets
        if (deleteSync(legacyVendorAssetsGlob, { force: true }))
            Logger("Legacy frontend vendor assets were deleted. Frontend vendor assets are now installed to 'app/assets'.");

        // See if there are any yarn packages
        // TODO Would be better to read in file here then hand it off since we can avoid redundant `existsSync` calls
        const yarnPaths = [];
        for (const sprinkle of sprinkles) {
            const path = sprinklesDir + sprinkle + "/package.json";
            if (existsSync(path)) yarnPaths.push(path);
        }

        if (yarnPaths.length > 0) {
            // Install yarn dependencies
            Logger("Installing vendor assets with Yarn...")

            // TODO I think we might be able to get away with removing this, since yarn.lock is synced with package.json
            deleteSync([vendorAssetsDir + "package.json", vendorAssetsDir + "yarn.lock"], { force: true });

            // Generate package.json
            const yarnTemplate = {
                // Private makes sure it isn't published, and cuts out a lot of unnecessary fields.
                private: true
            };
            Logger("Collating dependencies...");
            mergeYarnDeps(yarnTemplate, yarnPaths, vendorAssetsDir, doILog);
            Logger("Dependency collation complete.");

            // Perform installation
            // Yarn will automatically remove extraneous packages (barring algorithm failure)
            // --flat switch cannot be used currently due to https://github.com/yarnpkg/yarn/issues/1658 however "resolutions" thankfully will still work
            Logger("Running yarn install --non-interactive");
            execSync("yarn install --non-interactive", {
                cwd: vendorAssetsDir,
                stdio: doILog ? "inherit" : ""
            });

            // Ensure dependency tree is flat
            Logger("Inspecting dependency tree...");
            if (!yarnIsFlat(vendorAssetsDir, doILog)) {
                Logger(`
Dependency tree is not flat! Dependencies must be flat to prevent abnormal behavior of frontend dependencies.
Recommended solution is to adjust dependency versions until issue is resolved to ensure 100% compatibility.
Alternatively, resolutions can be used as an override, as documented at https://yarnpkg.com/en/docs/selective-version-resolutions
`);
                throw new Error("Dependency tree is not flat.");
            }
            else Logger("Dependency tree is flat and therefore usable.");
        }
        else {
            // Delete yarn artefacts
            deleteSync([
                vendorAssetsDir + "package.json",
                vendorAssetsDir + "node_modules/",
                vendorAssetsDir + "yarn.lock"
            ], { force: true });
        }

        // See if there are any Bower packages
        // TODO Would be better to read in file here then hand it off since we can avoid redundant `existsSync` calls
        const bowerPaths = [];
        for (const sprinkle of sprinkles) {
            const path = sprinklesDir + sprinkle + "/bower.json";
            if (existsSync(path)) {
                // TODO: We should really have a link to docs in the message
                console.warn(`DEPRECATED: Detected bower.json in ${sprinkle} Sprinkle. Support for bower (bower.json) will be removed in the future, please use npm/yarn (package.json) instead.`);
                bowerPaths.push(path);
            }
        }

        if (bowerPaths.length > 0) {
            // Install yarn dependencies
            Logger("Installing vendor assets with Bower...")

            // TODO I think we might be able to get away with removing this
            deleteSync(vendorAssetsDir + "bower.json", { force: true });

            // Generate package.json
            const bowerTemplate = {
                name: "uf-vendor-assets"
            };
            Logger("Collating dependencies...");
            mergeBowerDeps(bowerTemplate, bowerPaths, vendorAssetsDir, doILog);
            Logger("Dependency collation complete.");

            // Perform installation
            Logger("Running bower install -q --allow-root");
            // --allow-root stops bower from complaining about being in 'sudo' in various situations
            execSync("bower install -q --allow-root", {
                cwd: vendorAssetsDir,
                stdio: doILog ? "inherit" : ""
            });

            // Prune any unnecessary dependencies
            Logger("Running bower prune -q --allow-root");
            // --allow-root stops bower from complaining about being in 'sudo' in various situations
            execSync("bower prune -q --allow-root", {
                cwd: vendorAssetsDir,
                stdio: doILog ? "inherit" : ""
            });
        }
        else {
            // Remove bower artefacts
            deleteSync([
                vendorAssetsDir + "bower.json",
                vendorAssetsDir + "bower_components/"
            ], { force: true });
        }

        done();
    }
    catch (error) {
        done(error);
    }
});

/**
 * Compiles frontend assets. Mapped to npm script "uf-bundle".
 */
task("bundle", () => {
    // Build sources list
    const sources = [];
    for (const sprinkle of sprinkles) {
        sources.push(sprinklesDir + sprinkle + "/assets/**");
    }
    sources.push(vendorAssetsDir + "node_modules/**");
    sources.push(vendorAssetsDir + "bower_components/**");

    // Create bundle stream factories object
    const bundleBuilder = {
        Scripts: (src, name) => {
            return src
                .pipe(concatJs(name + ".js"))
                .pipe(minifyJs())
                .pipe(rev());
        },
        Styles: (src, name) => {
            return src
                .pipe(concatCss(name + ".css"))
                .pipe(minifyCss())
                .pipe(rev());
        }
    };

    // Load up bundle configurations
    const rawConfigs = [];
    for (const sprinkle of sprinkles) {
        Logger("Looking for asset bundles in sprinkle " + sprinkle);

        // Try to read file
        let fileContent;
        try {
            fileContent = readFileSync(sprinklesDir + sprinkle + "/" + sprinkleBundleFile);
            Logger(`   Read '${sprinkleBundleFile}'.`);
        }
        catch (error) {
            Logger(`   No '${sprinkleBundleFile}' detected, or can't be read.`);
            continue;
        }

        // Validate (JSON and content)
        let rawConfig;
        try {
            rawConfig = JSON.parse(fileContent);
            ValidateRawConfig(rawConfig);
            rawConfigs.push(rawConfig);
            Logger("   Asset bundles validated and loaded.");
        }
        catch (error) {
            Logger("   Asset bundle is invalid.");
            throw error;
        }
    }

    // Merge bundles
    Logger("Merging asset bundles...");
    const rawConfig = MergeRawConfigs(rawConfigs);

    // Set up virtual path rules
    rawConfig.VirtualPathRules = [
        ["../app/assets/node_modules", "./assets/vendor"],
        ["../app/assets/bower_components", "./assets/vendor"]];
    for (const sprinkle of sprinkles) {
        rawConfig.VirtualPathRules.push([
            sprinklesDir + sprinkle + "/assets", "./assets"
        ]);
    }

    // Set base path for bundle resources to align with virtual paths
    rawConfig.BundlesVirtualBasePath = "./assets/";

    // Bundle results callback
    function bundleResults(results) {
        /**
         * Resolves absolute path to gulp-uf-bundle-assets v2 style path
         * @param {string} path Absolute path to resolve.
         */
        function resolveToAssetPath(path) {
            if (path.startsWith(resolvePath(sprinklesDir))) {
                // Handle sprinkle path
                for (const sprinkle of sprinkles) {
                    const sprinklePath = resolvePath(sprinklesDir, sprinkle, "assets");
                    if (path.startsWith(sprinklePath)) {
                        return path.replace(sprinklePath, "").replace(/\\/g, "/").replace("/", "");
                    }
                }
            }
            else {
                // Handle vendor path
                if (path.startsWith(resolvePath(vendorAssetsDir, "bower_components"))) {
                    return path.replace(resolvePath(vendorAssetsDir, "bower_components"), "").replace(/\\/g, "/").replace("/", "");
                }
                else if (path.startsWith(resolvePath(vendorAssetsDir, "node_modules"))) {
                    return path.replace(resolvePath(vendorAssetsDir, "node_modules"), "").replace(/\\/g, "/").replace("/", "");
                }
            }

            throw new Error(`Unable to resolve path '${path}' to relative asset path.`);
        }

        const resultsObject = {};
        for (const [name, files] of results) {
            if (files.length !== 1)
                throw new Error(`The bundle ${name} has not generated exactly one file.`);
            else {
                if (!resultsObject[name]) {
                    resultsObject[name] = {};
                }
                if (files[0].extname === ".js")
                    resultsObject[name].scripts = resolveToAssetPath(files[0].path);
                else
                    resultsObject[name].styles = resolveToAssetPath(files[0].path);
            }
        }
        // Write file
        Logger("Rriting results file...");
        writeFileSync("./bundle.result.json", JSON.stringify(resultsObject));
        Logger("Done.")
    };

    // Open stream
    Logger("Starting bundle process proper...");
    return src(sources)
        .pipe(new Bundler(rawConfig, bundleBuilder, bundleResults))
        .pipe(prune(publicAssetsDir))
        .pipe(dest(publicAssetsDir));
});

/**
 * Run all frontend tasks.
 */
task("frontend", series("assets-install", "bundle"));

/**
 * 
 */
task("clean", (done) => {
    try {
        Logger("Cleaning vendor assets...");
        deleteSync(vendorAssetsDir, { force: true });
        Logger("Done.");

        Logger("Cleaning public assets...");
        deleteSync(publicAssetsDir, { force: true })
        Logger("Done.");

        done();
    }
    catch (error) {
        done(error);
    }
});
