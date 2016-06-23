var gutil = require('gulp-util'),
  logger = require('./service/logger'),
  warnPrefix = gutil.colors.bgYellow.black('WARN');

function Sourcemaps() {
}

Sourcemaps.prototype.verify = function (file, enc, cb) {
  if (!file.isStream() && // ignore streams (e.g. browserify) this those will implement their own sourcemaps
    (!file.sourceMap || !file.sourceMap.sources || !file.sourceMap.sources.length)) {
    // only log error in case user doesn't care about source maps.
    // often this will result with an error later on in the pipe anyways.
    logger.log("\n" +
      warnPrefix + " Source map is empty for file '" + gutil.colors.magenta(file.relative) + "'.\n" +
      warnPrefix + " This is most likely not a problem with '" + gutil.colors.cyan('gulp-bundle-assets') + "' itself.\n" +
      warnPrefix + " This usually happens when a file passing through the stream is invalid or malformed\n" +
      warnPrefix + " or if you have a misbehaving custom transform.\n");
  }
  this.push(file);
  cb();
};

/**
 * Support different variations of the option: map, maps, sourcemap, sourcemaps
 *
 * @param {Object} opts bundle config
 * @returns {boolean} whether to disabled sourcemaps
 */
Sourcemaps.prototype.isEnabled = function (opts) {
  // default is undefined, which returns true
  if (typeof opts.bundleOptions.maps !== 'undefined') {
    return opts.bundleOptions.maps !== false;
  }
  if (typeof opts.bundleOptions.map !== 'undefined') {
    return opts.bundleOptions.map !== false;
  }
  if (typeof opts.bundleOptions.sourcemap !== 'undefined') {
    return opts.bundleOptions.sourcemap !== false;
  }
  if (typeof opts.bundleOptions.sourcemaps !== 'undefined') {
    return opts.bundleOptions.sourcemaps !== false;
  }
  return true;
};

module.exports = new Sourcemaps();