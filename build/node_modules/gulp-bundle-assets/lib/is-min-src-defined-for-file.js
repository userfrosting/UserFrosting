var _ = require('lodash'),
  stringHelper = require('./string-helper');

module.exports = function (file, minSrcs, bundleName, type) {
  var isMinFile = false;
  if (minSrcs[bundleName] && minSrcs[bundleName][type]) {
    isMinFile = _.some(minSrcs[bundleName][type], function (obj) {
      // only doing simple endsWith matching here.
      // to be technically correct, should glob match in case
      // they put a glob in minSrc
      return stringHelper.endsWith(obj.src, file.relative) || stringHelper.endsWith(obj.minSrc, file.relative);
    });
  }
  return isMinFile;
};