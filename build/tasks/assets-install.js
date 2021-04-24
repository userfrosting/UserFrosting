// @ts-check
import { Logger, legacyVendorAssetsGlob, sprinkles, sprinklesDir, vendorAssetsDir } from "./util.js";
import { bower as mergeBowerDeps, npm as mergeNpmDeps } from "@userfrosting/merge-package-dependencies";
import browserifyDependencies from "@userfrosting/browserify-dependencies";
import { sync as deleteSync } from "del";
import childProcess, { exec as _exec } from "child_process";
import { existsSync } from "fs";
import { promisify } from "util";

// Promisify exec
const exec = promisify(_exec);

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
        log.info("Compiling compatible node modules into UMD bundles with browserify");
        deleteSync(vendorAssetsDir + "browser_modules/", { force: true });
        await browserifyDependencies({
            dependencies: Object.keys(pkg.dependencies),
            inputDir: vendorAssetsDir + "node_modules/",
            outputDir: vendorAssetsDir + "browser_modules/",
            silentFailures: true,
        });
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
