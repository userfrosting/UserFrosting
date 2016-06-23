var gulp = require('gulp'),
  gutil = require('gulp-util'),
  util = require('util'),
  path = require('path'),
  using = require('./using'),
  pathifySrc = require('./pathify-config-src');

function StreamCopy() {
}

// assume configBase will ALWAYS be defined (and defaulted to '.')
StreamCopy.prototype.getCustomBase = function (configBase, relativeBase) {
  if (!relativeBase) {
    return configBase;
  }
  return path.join(configBase, relativeBase);
};

/**
 * @param {String} item
 * @param base
 * @returns {*}
 */
StreamCopy.prototype.getStringCopyStream = function (item, base) {
  return gulp.src(pathifySrc(item, base), { base: base })
    .pipe(using.copy(base));
};

/**
 * @param {Object} item
 * @param base
 * @returns {*}
 */
StreamCopy.prototype.getObjectCopyStream = function (item, base) {
  return gulp.src(pathifySrc(item.src, base), { base: this.getCustomBase(base, item.base) })
    .pipe(using.copy(base));
};

StreamCopy.prototype.getCopyStream = function (item, base) {
  if (typeof item === 'string') {
    return this.getStringCopyStream(item, base);
  } else if (typeof item === 'object' && !util.isArray(item)) {
    return this.getObjectCopyStream(item, base);
  }
  this.throwUnsupportedSyntaxError();
};

StreamCopy.prototype.throwUnsupportedSyntaxError = function () {
  throw new gutil.PluginError('gulp-bundle-assets', 'Unsupported syntax for copy. See here for supported variations: ' +
    'https://github.com/dowjones/gulp-bundle-assets/blob/master/examples/copy/bundle.config.js');
};

// naturally a singleton because node's require caches the value assigned to module.exports
module.exports = new StreamCopy();