import gulplog from "gulplog";
import { readFileSync } from "fs";

// Relative to gulpfile.esm.js
const rootDir = "..";
export const sprinklesDir = `${rootDir}/app/sprinkles/`;
export const sprinklesSchemaPath = `${rootDir}/app/sprinkles.json`;
export const publicAssetsDir = `${rootDir}/public/assets/`;
export const legacyVendorAssetsGlob = `${rootDir}/sprinkles/*/assets/vendor/**`;
export const sprinkleBundleFile = "asset-bundles.json";
export const vendorAssetsDir = `${rootDir}/app/assets/`;
export const logFile = `${rootDir}/app/logs/build.log`;

// Load sprinkles
/** @type {string[]} */
let sprinkles;
try {
    sprinkles = JSON.parse(readFileSync(sprinklesSchemaPath).toString()).base;
}
catch (error) {
    gulplog.error(sprinklesSchemaPath + " could not be loaded, does it exist?");
    throw error;
}

export { sprinkles };
