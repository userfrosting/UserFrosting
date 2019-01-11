import browserifyDependencies from "@userfrosting/browserify-dependencies";
import Bundler, { MergeRawConfigs, ValidateRawConfig } from "@userfrosting/gulp-bundle-assets";
import { bower as mergeBowerDeps, npm as mergeNpmDeps } from "@userfrosting/merge-package-dependencies";
import { execSync } from "child_process";
import { sync as deleteSync } from "del";
import { config as envConfig } from "dotenv";
import { existsSync, readFileSync, writeFileSync } from "fs";
import gulp from "gulp";
import minifyCss from "gulp-clean-css";
import concatJs from "gulp-concat";
import concatCss from "gulp-concat-css";
import prune from "gulp-prune";
import rev from "gulp-rev";
import minifyJs from "gulp-uglify-es";
import { info } from "gulplog";
import { normalize as normalisePath, resolve as resolvePath } from "path";

// Load environment variables
envConfig({ path: "../app/.env" });

// Set up logging
const doILog = (process.env.UF_MODE === "dev");

/**
 * Prints to stdout with newline when UF_MODE is dev.
 * @param {any} message Message to log.
 */
function Logger(message, source) {
    if (doILog) {
        if (source)
            info(`${source}: ${message}`);
        else
            info(message);
    }
}

// Path constants
const rootDir = "../";
const sprinklesDir = rootDir + "app/sprinkles/";
const sprinklesSchemaPath = rootDir + "app/sprinkles.json";
const publicAssetsDir = rootDir + "public/assets/";
const legacyVendorAssetsGlob = rootDir + "sprinkles/*/assets/vendor/**";
const sprinkleBundleFile = "asset-bundles.json";
const vendorAssetsDir = rootDir + "app/assets/";
const buildDirFromVendorAssetsDir = "../../build/";

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
export async function assetsInstall() {
    // Clean up any legacy assets
    if (deleteSync(legacyVendorAssetsGlob, { force: true }))
        Logger("Legacy frontend vendor assets were deleted. Frontend vendor assets are now installed to 'app/assets'.");

    // See if there are any npm packages
    // TODO Would be better to read in file here then hand it off since we can avoid redundant `existsSync` calls
    const npmPaths = [];
    for (const sprinkle of sprinkles) {
        const path = sprinklesDir + sprinkle + "/package.json";
        if (existsSync(path)) npmPaths.push(path);
    }

    if (npmPaths.length > 0) {
        // Install npm dependencies
        Logger("Installing vendor assets with NPM...")

        // Remove package.json (package-lock.json can be left untouched as dates will invalidate it)
        deleteSync(vendorAssetsDir + "package.json", { force: true });

        // Generate package.json
        const npmTemplate = {
            // Private makes sure it isn't published, and cuts out a lot of unnecessary fields.
            private: true
        };
        Logger("Collating dependencies...");
        const pkg = mergeNpmDeps(npmTemplate, npmPaths, vendorAssetsDir, doILog);
        Logger("Dependency collation complete.");

        // Perform installation
        // NPM will automatically remove extraneous packages (barring algorithm failure) during install
        Logger("Running npm install (using npm from PATH)");
        execSync("npm install", {
            cwd: vendorAssetsDir,
            stdio: doILog ? "inherit" : ""
        });

        // Conduct audit
        Logger("Running npm audit (using npm from PATH)");
        try {
            execSync("npm audit", {
                cwd: vendorAssetsDir,
                stdio: doILog ? "inherit" : ""
            });
        }
        catch {
            Logger("There appear to be some vulerabilities within your dependencies. Updating is recommended.");
        }

        // Browserify dependencies
        Logger("Running browserify against npm dependencies with a compatible main entrypoint");
        deleteSync(vendorAssetsDir + "browser_modules/", { force: true });
        await browserifyDependencies({
            dependencies: Object.keys(pkg.dependencies),
            inputDir: vendorAssetsDir + "node_modules/",
            outputDir: vendorAssetsDir + "browser_modules/"
        })
    }
    else {
        // Delete npm artefacts
        deleteSync([
            vendorAssetsDir + "package.json",
            vendorAssetsDir + "node_modules/",
            vendorAssetsDir + "package-lock.json",
            vendorAssetsDir + "browser_modules/"
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
        // Install bower dependencies
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
        execSync(normalisePath(buildDirFromVendorAssetsDir + "node_modules/.bin/bower") + " install -q --allow-root", {
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
};

/**
 * Compiles frontend assets. Mapped to npm script "uf-bundle".
 */
export function bundle() {
    // Build sources list
    const sources = [];
    for (const sprinkle of sprinkles) {
        sources.push(sprinklesDir + sprinkle + "/assets/**");
    }
    sources.push(vendorAssetsDir + "node_modules/**");
    sources.push(vendorAssetsDir + "browser_modules/**");
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
            // Filter to compatible files (permits sourcemaps)
            const filteredFiles = [];
            for (const file of files) {
                if (file.extname === ".js" || file.extname === ".css") {
                    filteredFiles.push(file);
                }
            }

            if (filteredFiles.length !== 1)
                throw new Error(`The bundle ${name} has not generated exactly one file.`);
            else {
                if (!resultsObject[name]) {
                    resultsObject[name] = {};
                }
                if (filteredFiles[0].extname === ".js")
                    resultsObject[name].scripts = resolveToAssetPath(filteredFiles[0].path);
                else
                    resultsObject[name].styles = resolveToAssetPath(filteredFiles[0].path);
            }
        }
        // Write file
        Logger("Writing results file...");
        writeFileSync("./bundle.result.json", JSON.stringify(resultsObject));
        Logger("Finished writing results file.")
    };

    // Logger adapter
    function LoggerAdapter(message, loglevel) {
        // Normal level and above
        if (loglevel > 0) {
            Logger(message, "gulp-bundle-assets");
        }
    }
    rawConfig.Logger = LoggerAdapter;

    // Open stream
    Logger("Starting bundle process proper...");
    return gulp.src(sources, { sourcemaps: true })
        .pipe(new Bundler(rawConfig, bundleBuilder, bundleResults))
        .pipe(prune(publicAssetsDir))
        .pipe(gulp.dest(publicAssetsDir, { sourcemaps: "." }));
};

/**
 * Run all frontend tasks.
 */
export const frontend = gulp.series(assetsInstall, bundle);

/**
 * Clean vendor and public asset folders.
 * @param {() => {}} done Used to mark task completion.
 */
export function clean(done) {
    try {
        Logger("Cleaning vendor assets...");
        deleteSync(vendorAssetsDir, { force: true });
        Logger("Finished cleaning vendor assets.");

        Logger("Cleaning public assets...");
        deleteSync(publicAssetsDir, { force: true })
        Logger("Finsihed cleaning public assets.");

        done();
    }
    catch (error) {
        done(error);
    }
};
