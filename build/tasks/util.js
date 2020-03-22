// @ts-check
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
let sprinkles;
try {
    sprinkles = JSON.parse(readFileSync(sprinklesSchemaPath).toString()).base;
}
catch (error) {
    gulplog.info(sprinklesSchemaPath + " could not be loaded, does it exist?");
    throw error;
}

export { sprinkles };

/**
 * Log adapter for "ts-log" to "gulplog".
 */
export class Logger {
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
    log(logFunc, message, optionalParams) {
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
        this.log(gulplog.debug, message, optionalParams);
    }

    /**
     * Trace log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    trace(message, ...optionalParams) {
        this.log(gulplog.debug, message, optionalParams);
    }

    /**
     * "Standard" log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    info(message, ...optionalParams) {
        this.log(gulplog.info, message, optionalParams);
    }

    /**
     * Warning log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    warn(message, ...optionalParams) {
        this.log(gulplog.warn, message, optionalParams);
    }

    /**
     * Error log level.
     * @param {string} message Message to log.
     * @param  {...any} optionalParams Values to log, encoded with `JSON.stringify`.
     */
    error(message, ...optionalParams) {
        this.log(gulplog.error, message, optionalParams);
    }
}
