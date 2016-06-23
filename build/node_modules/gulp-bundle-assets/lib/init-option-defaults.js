var BundleKeys = require('./model/bundle-keys'),
  gutil = require('gulp-util'),
  defaults = require('lodash').defaults;

var sourcemapDefaults = {
  init: {loadMaps: true},
  write: {},
  destPath: 'maps'
};

module.exports = function (bundle) {
  bundle[BundleKeys.OPTIONS] = bundle[BundleKeys.OPTIONS] || {};
  bundle[BundleKeys.OPTIONS].transforms = bundle[BundleKeys.OPTIONS].transforms || {};
  bundle[BundleKeys.OPTIONS].transforms[BundleKeys.SCRIPTS] = bundle[BundleKeys.OPTIONS].transforms[BundleKeys.SCRIPTS] || gutil.noop;
  bundle[BundleKeys.OPTIONS].transforms[BundleKeys.STYLES] = bundle[BundleKeys.OPTIONS].transforms[BundleKeys.STYLES] || gutil.noop;
  bundle[BundleKeys.OPTIONS].watch = bundle[BundleKeys.OPTIONS].watch || {};
  bundle[BundleKeys.OPTIONS].pluginOptions = bundle[BundleKeys.OPTIONS].pluginOptions || {};
  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-minify-css'] = bundle[BundleKeys.OPTIONS].pluginOptions['gulp-minify-css'] || {};
  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-uglify'] = bundle[BundleKeys.OPTIONS].pluginOptions['gulp-uglify'] || {};
  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-concat'] = bundle[BundleKeys.OPTIONS].pluginOptions['gulp-concat'] || {};

  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'] = defaults(bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'] || {}, sourcemapDefaults);

  // This is to get a clone of the base options
  var sourcemapBaseOpts = defaults({}, bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps']);
  // Remove the style and script sub attributes
  delete sourcemapBaseOpts[BundleKeys.SCRIPTS];
  delete sourcemapBaseOpts[BundleKeys.STYLES];

  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'][BundleKeys.SCRIPTS] = defaults(bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'][BundleKeys.SCRIPTS] || {}, sourcemapBaseOpts);
  bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'][BundleKeys.STYLES] = defaults(bundle[BundleKeys.OPTIONS].pluginOptions['gulp-sourcemaps'][BundleKeys.STYLES] || {}, sourcemapBaseOpts);

  bundle[BundleKeys.OPTIONS].order = bundle[BundleKeys.OPTIONS].order || {};
};