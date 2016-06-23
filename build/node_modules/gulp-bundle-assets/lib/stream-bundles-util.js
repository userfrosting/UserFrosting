var logger = require('./service/logger'),
  gutil = require('gulp-util'),
  warnPrefix = gutil.colors.bgYellow.black('WARN');

function StreamBundlesUtil() {
}

StreamBundlesUtil.prototype.warnIfNoBundleProperty = function (config) {
  if (config && !config.bundle && config.file && config.file.relative) { // can guarantee !!file b/c (config instanceof Config)
    logger.log(warnPrefix, "No '" + gutil.colors.cyan('bundle') +
      "' property found in " + gutil.colors.magenta(config.file.relative) + ". Did you mean to define one?");
  }
};

// naturally a singleton because node's require caches the value assigned to module.exports
module.exports = new StreamBundlesUtil();