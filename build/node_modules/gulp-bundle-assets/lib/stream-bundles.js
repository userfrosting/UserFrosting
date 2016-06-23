var util = require('util'),
  BundleKeys = require('./model/bundle-keys'),
  gutil = require('gulp-util'),
  logger = require('./service/logger'),
  _ = require('lodash'),
  pathifySrc = require('./pathify-config-src'),
  stringHelper = require('./string-helper'),
  bundleAllEnvironments = require('./bundle-all-environments'),
  initOptionDefaults = require('./init-option-defaults'),
  streamFiles = require('./stream-files'),
  streamCopy = require('./stream-copy'),
  streamBundlesUtil = require('./stream-bundles-util');

function _bundle(config, env) {
  var streams = [],
    bundles = config.bundle,
    isBundleAll = config.options && config.options.bundleAllEnvironments,
    base = (config.options) ? config.options.base : '.', // can guarantee !!options b/c (config instanceof Config)
    minSrcs = (config.getAllMinSrcs) ? config.getAllMinSrcs() : {};

  if (env) {
    logger.log('Creating bundle(s) for environment "' + env + '"');
  }

  _.forEach(Object.keys(bundles), function (bundleName) {

    var namedBundleObj = bundles[bundleName];
    initOptionDefaults(namedBundleObj);

    _.forEach(Object.keys(namedBundleObj), function (type) {

      /* jshint -W035 */
      if (type === BundleKeys.SCRIPTS || type === BundleKeys.STYLES) {
        streams.push(streamFiles[type]({
          src: pathifySrc(namedBundleObj[type], base, namedBundleObj[BundleKeys.OPTIONS], env),
          base: base,
          env: env,
          type: type,
          bundleName: bundleName,
          bundleOptions: namedBundleObj[BundleKeys.OPTIONS],
          isBundleAll: isBundleAll,
          minSrcs: minSrcs
        }));
      } else if (type === BundleKeys.OPTIONS) {
        // ok
      } else {
        throw new gutil.PluginError('gulp-bundle-assets', 'Unsupported object key found: "bundle.' +
          bundleName + '.' + type + '". Supported types are "' +
          BundleKeys.SCRIPTS + '", "' + BundleKeys.STYLES + '" and "' + BundleKeys.OPTIONS + '"');
      }
      /* jshint +W035 */

    });

  });

  return streams;
}

function _copy(config) {
  var streams = [],
    copy = config.copy,
    base = (config.options) ? config.options.base : '.'; // can guarantee !!options b/c (config instanceof Config)

  if (typeof copy === 'string') {
    streams.push(streamCopy.getStringCopyStream(copy, base));
  } else if (util.isArray(copy)) {
    _.forEach(copy, function (item) {
      streams.push(streamCopy.getCopyStream(item, base));
    });
  } else if (typeof copy === 'object') {
    streams.push(streamCopy.getObjectCopyStream(copy, base));
  } else {
    streamCopy.throwUnsupportedSyntaxError();
  }

  return streams;
}


function bundle(config) {
  var streams = [];

  if (config.bundle) {
    if (config.options && config.options.bundleAllEnvironments) { // can guarantee !!options b/c (config instanceof Config)
      streams = streams.concat(bundleAllEnvironments(config, _bundle));
    } else {
      streams = streams.concat(_bundle(config, process.env.NODE_ENV));
    }
  }

  streamBundlesUtil.warnIfNoBundleProperty(config);

  if (config.copy) {
    streams = streams.concat(_copy(config));
  }

  return streams;
}

module.exports = bundle;