// @ts-check
import gulp from "gulp";
import minifyCss from "gulp-clean-css";
import concatJs from "gulp-concat";
import concatCss from "gulp-concat-css";
import prune from "gulp-prune";
import rev from "gulp-rev";
import minifyJs from "gulp-uglify-es";
import { resolve as resolvePath } from "path";
import Bundler, { MergeRawConfigs, ValidateRawConfig } from "@userfrosting/gulp-bundle-assets";
import { readFileSync, writeFileSync } from "fs";
import { src } from "@userfrosting/vinyl-fs-vpath";
import { Logger, vendorAssetsDir, sprinklesDir, sprinkles, sprinkleBundleFile, publicAssetsDir } from "./util.js";

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

    // Set up virtual path rules
    /** @type {import("@userfrosting/vinyl-fs-vpath").IVirtPathMapping[]} */
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