var path = require('path'),
  _ = require('lodash'),
  deepPluck = require('../deep-pluck'),
  deepPluckParent = require('../deep-pluck-parent'),
  BundleType = require('./bundle-type');

module.exports = Config;

function Config(file, options) {

  var userConfig;

  if (isInstanceOfVinylFile(file)) {
    userConfig = require(file.path);
    userConfig.file = {
      cwd: file.cwd,
      base: file.base,
      path: file.path,
      relative: file.relative
    };
  } else {
    userConfig = file;
  }

  if (!userConfig || !(userConfig.bundle || userConfig.copy)) {
    throw new Error('Configuration file should be in the form "{ bundle: {}, copy: {} }"');
  }
  this.options = _.merge({
    base: '.'
  }, options);
  _.merge(this, userConfig);

}

// dumb way to do instanceof so this module works when required from other modules
// since `file` will be an instance of an object from a somewhere else
function isInstanceOfVinylFile(file) {
  return file && file.isBuffer && file.pipe;
}

function isString(val) {
  return typeof val === 'string';
}

/**
 * Returns uniq list of all environments defined in config file
 * @returns {Array}
 */
Config.prototype.getAllEnvironments = function () {
  return _.chain(['uglify', 'rev', 'useMin'])
    .map(deepPluck, this)
    .flatten(true)
    .uniq()
    .filter(isString)
    .value();
};

Config.prototype.getAllMinSrcs = function () {

  var self = this,
    minSrcs = {};

  _.forEach(Object.keys(this.bundle), function (bundleKey) {
    minSrcs[bundleKey] = minSrcs[bundleKey] || {};

    if (self.bundle[bundleKey][BundleType.SCRIPTS]) {
      minSrcs[bundleKey][BundleType.SCRIPTS] = _(['minSrc'])
        .map(deepPluckParent, self.bundle[bundleKey][BundleType.SCRIPTS])
        .flatten()
        .value();
    }

    if (self.bundle[bundleKey][BundleType.STYLES]) {
      minSrcs[bundleKey][BundleType.STYLES] = _(['minSrc'])
        .map(deepPluckParent, self.bundle[bundleKey][BundleType.STYLES])
        .flatten()
        .value();
    }
  });

  return minSrcs;
};