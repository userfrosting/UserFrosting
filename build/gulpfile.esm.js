import browserifyDependencies from "@userfrosting/browserify-dependencies";
import Bundler, { MergeRawConfigs, ValidateRawConfig } from "@userfrosting/gulp-bundle-assets";
import { bower as mergeBowerDeps, npm as mergeNpmDeps } from "@userfrosting/merge-package-dependencies";
import childProcess, { exec as _exec } from "child_process";
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
import gulplog from "gulplog";
import { resolve as resolvePath } from "path";
import stripAnsi from "strip-ansi";
import { promisify } from "util";
import { src } from "@userfrosting/vinyl-fs-vpath";

// Promisify exec
const exec = promisify(_exec);

// Path constants
const rootDir = "..";
const sprinklesDir = `${rootDir}/app/sprinkles/`;
const sprinklesSchemaPath = `${rootDir}/app/sprinkles.json`;
const publicAssetsDir = `${rootDir}/public/assets/`;
const legacyVendorAssetsGlob = `${rootDir}/sprinkles/*/assets/vendor/**`;
const sprinkleBundleFile = "asset-bundles.json";
const vendorAssetsDir = `${rootDir}/app/assets/`;
const logFile = `${rootDir}/app/logs/build.log`;

// Load environment variables
envConfig({ path: "../app/.env" });

// Set up logging

// Write starting command to log
writeFileSync(logFile, "\n\n" + process.argv.join(" ") + "\n\n", {
    flag: 'a'
});

// Catch stdout and write to build log
const write = process.stdout.write;
const w = (...args) => {
    process.stdout.write = write;
    process.stdout.write(...args);

    writeFileSync(logFile, stripAnsi(args[0]), {
        flag: 'a'
    });

    process.stdout.write = w;
};
process.stdout.write = w;

/**
 * Log adapter for "ts-log" to "gulplog".
 */
class Logger {
    /**
     * @param {string} source 
     */
    constructor(source) {
        this.source = source;
    }

    /**
     * Composes complete message to log.
     * @private
     * @param {(x: string) => void} logFunc Logging function.
     * @param {string} message Message to log.
     * @param {any[]} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    compose(logFunc, message, optionalParams) {
        const messageLines = message.split("\n");
        
        if (optionalParams.length > 0) {
            if (messageLines.length > 1) {
                messageLines.push(JSON.stringify(optionalParams));
            } else {
                messageLines[0] = `${messageLines[0]} ${JSON.stringify(optionalParams)}`;
            }
        }

        for (const messageLine of messageLines) {
            logFunc(`${this.source}: ${messageLine}`)
        }
    }

    /**
     * Debug log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    debug(message, ...optionalParams) {
        this.compose(gulplog.debug, message, optionalParams);
    }

    /**
     * Trace log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    trace(message, ...optionalParams) {
        this.compose(gulplog.debug, message, optionalParams);
    }

    /**
     * "Standard" log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    info(message, ...optionalParams) {
        this.compose(gulplog.info, message, optionalParams);
    }

    /**
     * Warning log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    warn(message, ...optionalParams) {
        this.compose(gulplog.warn, message, optionalParams);
    }

    /**
     * Error log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    error(message, ...optionalParams) {
        this.compose(gulplog.error, message, optionalParams);
    }
}

/**
 * Runs the provided command and captures output.
 * @param {string} source Used to annotate logs.
 * @param {string} cmd Command to execute.
 * @param {childProcess.ExecOptions} options Options to pass to `exec`.
 */
async function runCommand(source, cmd, options) {
    const log = new Logger(`${source}> ${cmd}`)
    log.info("Running command");

    try {
        const result = await exec(cmd, options);
        if (result.stdout) log.info(result.stdout);
        if (result.stderr) log.error(result.stderr);
    } catch (e) {
        if (e.stdout) log.info(e.stdout);
        if (e.stderr) log.error(e.stderr);
        log.error("Command has completed with an error");
        throw e;
    }

    log.info("Command has completed successfully");
}

// Load sprinkles
let sprinkles;
try {
    sprinkles = JSON.parse(readFileSync(sprinklesSchemaPath)).base;
}
catch (error) {
    gulplog.info(sprinklesSchemaPath + " could not be loaded, does it exist?");
    throw error;
}

/**
 * Installs vendor assets. Mapped to npm script "uf-assets-install".
 */
export async function assetsInstall() {
    const log = new Logger(assetsInstall.name);

    // Clean up any legacy assets
    if (deleteSync(legacyVendorAssetsGlob, { force: true }))
        log.info("Legacy frontend vendor assets were deleted. Frontend vendor assets are now installed to 'app/assets'.");

    // See if there are any npm packages
    // TODO Would be better to read in file here then hand it off since we can avoid redundant `existsSync` calls
    const npmPaths = [];
    for (const sprinkle of sprinkles) {
        const path = sprinklesDir + sprinkle + "/package.json";
        if (existsSync(path)) npmPaths.push(path);
    }

    if (npmPaths.length > 0) {
        // Install npm dependencies
        log.info("Installing vendor assets with NPM...")

        // Remove package.json and package-lock.json (if it happens to exist)
        deleteSync(vendorAssetsDir + "package.json", { force: true });
        deleteSync(vendorAssetsDir + "package-lock.json", { force: true });

        // Generate package.json
        const npmTemplate = {
            // Private makes sure it isn't published, and cuts out a lot of unnecessary fields.
            private: true
        };
        log.info("Collating dependencies...");
        const pkg = mergeNpmDeps(npmTemplate, npmPaths, vendorAssetsDir, true);
        log.info("Dependency collation complete.");

        log.info("Using npm from PATH variable");

        // Remove any existing unneeded dependencies
        log.info("Removing extraneous dependencies");
        await runCommand(assetsInstall.name, "npm prune", { cwd: vendorAssetsDir });

        // Perform installation
        log.info("Installing dependencies");
        await runCommand(assetsInstall.name, "npm install", { cwd: vendorAssetsDir });

        // Conduct audit
        log.info("Running audit");
        try {
            await runCommand(assetsInstall.name, "npm audit", { cwd: vendorAssetsDir });
        }
        catch {
            log.warn("There appear to be some vulerabilities within your dependencies. Updating is recommended.");
        }

        // Browserify dependencies
        log.info("Running browserify against npm dependencies with a compatible main entrypoint");
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
            log.warn(`DEPRECATED: Detected bower.json in ${sprinkle} Sprinkle. Support for bower (bower.json) will be removed in the future, please use npm/yarn (package.json) instead.`);
            bowerPaths.push(path);
        }
    }

    if (bowerPaths.length > 0) {
        // Install bower dependencies
        log.info("Installing vendor assets with Bower...")

        // TODO I think we might be able to get away with removing this
        deleteSync(vendorAssetsDir + "bower.json", { force: true });

        // Generate package.json
        const bowerTemplate = {
            name: "uf-vendor-assets"
        };
        log.info("Collating dependencies...");
        mergeBowerDeps(bowerTemplate, bowerPaths, vendorAssetsDir, true);
        log.info("Dependency collation complete.");

        // Perform installation
        log.info("Installed dependencies");
        // --allow-root stops bower from complaining about being in 'sudo' in various situations
        await runCommand(assetsInstall.name, "bower install -q --allow-root", { cwd: vendorAssetsDir });


        // Prune any unnecessary dependencies
        log.info("Removing extraneous dependencies");
        // --allow-root stops bower from complaining about being in 'sudo' in various situations
        await runCommand(assetsInstall.name, "bower prune -q --allow-root", { cwd: vendorAssetsDir });
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
    const log = new Logger(bundle.name);

    // Build sources list
    const sources = [];

    // Inputs
    for (const sprinkle of sprinkles) {
        sources.push(sprinklesDir + sprinkle + "/assets/**");
    }
    sources.push(vendorAssetsDir + "node_modules/**");
    sources.push(vendorAssetsDir + "browser_modules/**");
    sources.push(vendorAssetsDir + "bower_components/**");

    // Exclusions
    sources.push("!**.php");

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
        log.info("Looking for asset bundles in sprinkle " + sprinkle);

        // Try to read file
        let fileContent;
        try {
            fileContent = readFileSync(sprinklesDir + sprinkle + "/" + sprinkleBundleFile);
            log.info(`Read '${sprinkleBundleFile}'.`, sprinkle);
        }
        catch (error) {
            log.info(`No '${sprinkleBundleFile}' detected, or can't be read.`, sprinkle);
            continue;
        }

        // Validate (JSON and content)
        let rawConfig;
        try {
            rawConfig = JSON.parse(fileContent);
            ValidateRawConfig(rawConfig);
            rawConfigs.push(rawConfig);
            log.info("Asset bundles validated and loaded.", sprinkle);
        }
        catch (error) {
            log.error("Asset bundle is invalid.", sprinkle);
            throw error;
        }
    }

    // Merge bundles
    log.info("Merging asset bundles...");
    const rawConfig = MergeRawConfigs(rawConfigs);

    // Set up virtual path rules
    /** @type {import("@userfrosting/vinyl-fs-vpath").IVirtualPathMapping[]} */
    const virtPathMaps = [
        { match: "../app/assets/node_modules", replace: "../public/assets/vendor" },
        { match: "../app/assets/browser_modules", replace: "../public/assets/vendor" },
        { match: "../app/assets/bower_components", replace: "../public/assets/vendor" },
    ];
    for (const sprinkle of sprinkles) {
        virtPathMaps.push({
            match: sprinklesDir + sprinkle + "/assets",
            replace: "../public/assets"
        });
    }

    /** @type {import("@userfrosting/gulp-bundle-assets").ResultsCallback} */
    const resultsCallback = function (results) {
        /** @type {{ [x: string]: { scripts?: string, styles?: string, } }} */
        const resultsObject = {};

        // Styles
        for (const [name, files] of results.styles) {
            if (!(name in resultsObject)) {
                resultsObject[name] = {};
            }
            for (const file of files) {
                resultsObject[name].styles = resolvePath(file.path);
            }
        }

        // Scripts
        for (const [name, files] of results.scripts) {
            if (!(name in resultsObject)) {
                resultsObject[name] = {};
            }
            for (const file of files) {
                resultsObject[name].scripts = resolvePath(file.path);
            }
        }

        // Write file
        log.info("Writing results file...");
        writeFileSync("./bundle.result.json", JSON.stringify(resultsObject));
        log.info("Finished writing results file.")
    };

    rawConfig.Logger = new Logger(`${bundle.name} - @userfrosting/gulp-bundle-assets`);
    rawConfig.cwd = "../public/assets"

    // Open stream
    log.info("Starting bundle process proper...");
    return src({ globs: sources, virtPathMaps, base: '../public/assets' })
        .pipe(new Bundler(rawConfig, bundleBuilder, resultsCallback))
        .pipe(prune(publicAssetsDir))
        .pipe(gulp.dest('../public/assets/'));
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
    const log = new Logger(clean.name);

    try {
        log.info("Cleaning vendor assets...");
        deleteSync(vendorAssetsDir, { force: true });
        log.info("Finished cleaning vendor assets.");

        log.info("Cleaning public assets...");
        deleteSync(publicAssetsDir, { force: true })
        log.info("Finsihed cleaning public assets.");

        done();
    }
    catch (error) {
        done(error);
    }
};
