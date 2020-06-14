// @ts-check
import { sync as deleteSync } from "del";
import { vendorAssetsDir, publicAssetsDir, Logger } from "./util.js";

/**
 * Clean vendor and public asset folders.
 * @param {(err?) => {}} done Used to mark task completion.
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
