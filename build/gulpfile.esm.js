// @ts-check
import { exec as _exec } from "child_process";
import { config as envConfig } from "dotenv";
import { writeFileSync } from "fs";
import gulp from "gulp";
import stripAnsi from "strip-ansi";
import { logFile } from "./tasks/util.js";
import { assetsInstall } from "./tasks/assets-install.js";
import { bundle } from "./tasks/bundle.js";

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
    // @ts-ignore
    process.stdout.write(...args);
    
    writeFileSync(logFile, stripAnsi(args[0]), {
        flag: 'a'
    });
    
    // @ts-ignore
    process.stdout.write = w;
};
// @ts-ignore
process.stdout.write = w;

export { assetsInstall, bundle };

/**
 * Run all frontend tasks.
 */
export const frontend = gulp.series(assetsInstall, bundle);

export { clean } from "./tasks/clean.js";
