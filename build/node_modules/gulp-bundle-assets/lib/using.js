var gutil = require('gulp-util'),
  logger = require('./service/logger'),
  through = require('through2'),
  stringHelper = require('./string-helper');

module.exports.bundleName = function(key, type, env, bundleAllEnvironments) {
  var envPrefix = '';
  if (bundleAllEnvironments) {
    envPrefix = env ? env + '.' : 'default.';
  }
  return envPrefix + key + '.' + type;
};

module.exports.bundle = function (key, type, env, bundleAllEnvironments) {
  var bundleName = this.bundleName(key, type, env, bundleAllEnvironments);
  var prefix = "Bundle '" + gutil.colors.green(bundleName) + "' using";
  return through.obj(function (file, enc, cb) {
    logger.log(prefix, gutil.colors.magenta(file.relative));
    this.push(file);
    cb();
  });
};

module.exports.copy = function (base) {
  return through.obj(function (file, enc, cb) {
    var pathReplace = (base === '.') ? file.cwd : base;
    if (!stringHelper.endsWith(pathReplace, '/')) {
      pathReplace += '/';
    }
    logger.log("Copy file", gutil.colors.magenta(file.path.replace(pathReplace, '')));
    this.push(file);
    cb();
  });
};