var isMinSrcDefinedForFile = require('./is-min-src-defined-for-file'),
  isOptionEnabled = require('./is-option-enabled');

module.exports.getMinCssOption = function(opts) {
  // support both "minCSS" and "minCss" in case of typo
  var minCss = opts.bundleOptions.minCSS;
  if (typeof minCss === 'undefined') {
    minCss = opts.bundleOptions.minCss;
  }
  return minCss;
};

module.exports.css = function (file, opts) {
  return !isMinSrcDefinedForFile(file, opts.minSrcs, opts.bundleName, opts.type) &&
    isOptionEnabled(this.getMinCssOption(opts), opts.env);
};

module.exports.js = function (file, opts) {
  return !isMinSrcDefinedForFile(file, opts.minSrcs, opts.bundleName, opts.type) &&
    isOptionEnabled(opts.bundleOptions.uglify, opts.env);
};