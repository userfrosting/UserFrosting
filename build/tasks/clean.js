import { sync as deleteSync } from "del";
import { vendorAssetsDir, publicAssetsDir } from "./util.js";
import { GulpLogLogger } from "@userfrosting/ts-log-adapter-gulplog";

/**
 * Clean vendor and public asset folders.
 * @param {(err?: unknown) => {}} done Used to mark task completion.
 */
export function clean(done) {
    const log = new GulpLogLogger(clean.name);

    try {
        log.info("Cleaning vendor assets...");
        deleteSync(vendorAssetsDir, { force: true });
        log.info("Finished cleaning vendor assets.");

        log.info("Cleaning public assets...");
        deleteSync(publicAssetsDir, { force: true })
        log.info("Finished cleaning public assets.");

        done();
    }
    catch (error) {
        log.error("Completed with error");
        done(error);
    }
};
