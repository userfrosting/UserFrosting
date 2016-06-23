var through = require('through2'),
  path = require('path'),
  Bundle = require('./../model/bundle');

module.exports = function(key, type, resultOptions, env, bundleAllEnvironments) {
  return through.obj(function (file, enc, cb) {
    if (path.extname(file.path) !== '.map') { // ignore .map files
      file.bundle = new Bundle({
        name: key,
        type: type,
        result: resultOptions,
        env: env,
        bundleAllEnvironments: bundleAllEnvironments
      });
    }
    this.push(file);
    cb();
  });
};