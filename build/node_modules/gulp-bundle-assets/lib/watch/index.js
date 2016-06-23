var ConfigModel = require('../model/config'),
  cache = require('../service/cache'),
  streamBundlesWatch = require('../stream-bundles-watch'),
  gutil = require('gulp-util');

module.exports = function (opts) {
  var configFile,
    config;
  opts = opts || {};
  if (!opts.configPath) {
    throw new gutil.PluginError('gulp-bundle-assets', 'configPath option is required when watching');
  }
  if (!opts.dest) {
    throw new gutil.PluginError('gulp-bundle-assets', 'dest option is required when watching');
  }
  
  try {
    configFile = require(opts.configPath);
    config = new ConfigModel(configFile, opts);
  } catch (e) {
    gutil.log(e);
    throw new gutil.PluginError('gulp-bundle-assets', 'Failed to parse config file');
  }

  cache.set('config', config);

  streamBundlesWatch(config);
};