// @ts-check
import gulp from "gulp";
import minifyCss from "gulp-clean-css";
import concatJs from "gulp-concat";
import concatCss from "gulp-concat-css";
import prune from "gulp-prune";
import rev from "gulp-rev";
import minifyJs from "gulp-terser";
import { relative as relativePath } from "path";
import Bundler, { MergeRawConfigs, ValidateRawConfig } from "@userfrosting/gulp-bundle-assets";
import { readFileSync, writeFileSync } from "fs";
import { src } from "@userfrosting/vinyl-fs-vpath";
import { Logger, vendorAssetsDir, sprinklesDir, sprinkles, sprinkleBundleFile, publicAssetsDir } from "./util.js";
import gulpIf from "gulp-if";
import gulpSourcemaps from "gulp-sourcemaps";

/**
 * Compiles frontend assets. Mapped to npm script "uf-bundle".
 */
export function build() {
    const log = new Logger(build.name);

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
            rawConfig = JSON.parse(fileContent.toString());
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

    // Set up virtual path mappings
    /** @type {import("@userfrosting/vinyl-fs-vpath").IPathMapper[]} */
    const pathMappings = [
        { match: `${vendorAssetsDir}node_modules`, replace: `${publicAssetsDir}vendor` },
        { match: `${vendorAssetsDir}browser_modules`, replace: `${publicAssetsDir}vendor` },
        { match: `${vendorAssetsDir}bower_components`, replace: `${publicAssetsDir}vendor` },
    ];
    for (const sprinkle of sprinkles) {
        pathMappings.push({
            match: sprinklesDir + sprinkle + "/assets",
            replace: publicAssetsDir
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
                // Last one wins?
                resultsObject[name].styles = relativePath(publicAssetsDir, file.path).replace(/\\/g, "/");
            }
        }

        // Scripts
        for (const [name, files] of results.scripts) {
            if (!(name in resultsObject)) {
                resultsObject[name] = {};
            }
            for (const file of files) {
                // Last one wins?
                resultsObject[name].scripts = relativePath(publicAssetsDir, file.path).replace(/\\/g, "/");
            }
        }

        // Write file
        log.info("Writing results file...");
        writeFileSync("./bundle.result.json", JSON.stringify(resultsObject));
        log.info("Finished writing results file.")
    };

    rawConfig.Logger = new Logger(`${build.name} - @userfrosting/gulp-bundle-assets`);
    rawConfig.cwd = publicAssetsDir;

    // Open stream
    log.info("Starting bundle process proper...");
    return src({ globs: sources, pathMappings, base: publicAssetsDir })
        .pipe(gulpSourcemaps.init({ loadMaps: true }))
        .pipe(gulpIf(stylesAndScriptsFilter, new Bundler(rawConfig, bundleBuilder, resultsCallback)))
        .pipe(prune(publicAssetsDir))
        .pipe(gulpIf(
            stylesAndScriptsFilter,
            gulp.dest(publicAssetsDir, { sourcemaps: true }),
            gulp.dest(publicAssetsDir)
        ));
};

/**
 * Used to filter to just styles and scripts.
 * @param {import("vinyl").NullFile} fs
 */
function stylesAndScriptsFilter(fs) {
    return scriptsFilter(fs) || stylesFilter(fs);
}

/**
 * Used to filter to just styles.
 * @param {import("vinyl").NullFile} fs
 */
function stylesFilter(fs) {
    return fs.extname === ".css";
}

/**
 * Used to filter to just scripts.
 * @param {import("vinyl").NullFile} fs
 */
function scriptsFilter(fs) {
    return fs.extname === ".js";
}
