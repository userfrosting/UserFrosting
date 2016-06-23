var toResultType = require('./type');

/**
 * add bundle defined in file.bundle to obj
 * @param {Object} obj
 * @param {File} file
 * @param {string} pathPrefix
 */
module.exports = function(obj, file, pathPrefix, fileName) {
  fileName = fileName || 'bundle.result';

  if (file.bundle) {
    var env = file.bundle.env && file.bundle.bundleAllEnvironments ? file.bundle.env : '';
    var envKey = env || 'default';
    var bundleResultFileName = fileName + (env ? '.' + env : '') + '.json';
    obj[envKey] = obj[envKey] || {
      filename: bundleResultFileName,
      contents: {}
    };
    obj[envKey].contents[file.bundle.name] = obj[envKey].contents[file.bundle.name] || {};
    obj[envKey].contents[file.bundle.name][file.bundle.type] = toResultType(file, pathPrefix);
  }
  return obj;
};
