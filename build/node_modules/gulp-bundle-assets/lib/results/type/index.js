var gutil = require('gulp-util'),
  logger = require('../../service/logger'),
  path = require('path'),
  BundleResult = require('../../model/bundle-result'),
  warnPrefix = gutil.colors.bgYellow.black('WARN');

module.exports = function(file, pathPrefix) {
  var bundleType = file.bundle.type;
  var resulter = loadResulter(file.bundle.result.type, bundleType);
  var bundlePath = (pathPrefix) ? pathPrefix + file.relative : file.relative;
  return resulter(bundlePath.replace(/\\/g, '/')); // force result.json uri to never have backslashes, even on posix systems
};

/**
 * @param {string|Object|Function} resulter
 * @param bundleType
 */
function loadResulter(resulter, bundleType) {
  if (typeof resulter === 'function') {
    return resulter;
  }
  if (typeof resulter === 'object') {
    if (!resulter[bundleType]) {
      return loadResulter(BundleResult.DEFAULT, bundleType);
    }
    return loadResulter(resulter[bundleType], bundleType);
  }
  if (typeof resulter === 'string') {
    var resulterPath = path.join(__dirname + '/' + bundleType + '-' + resulter + '.js');
    try {
      return loadResulter(require(resulterPath), bundleType);
    } catch(e) {
      var defaultResulterPath = path.join(__dirname + '/' + bundleType + '-html.js');
      logger.log("\n" +
      warnPrefix + " Failed to load result writer: " + resulterPath + "\n" +
        warnPrefix + " using default instead: " + defaultResulterPath);
      return loadResulter(require(defaultResulterPath), bundleType);
    }
  }
  throw new Error('Failed to load result function "' + resulter + '" for type "' + bundleType + '"');
}