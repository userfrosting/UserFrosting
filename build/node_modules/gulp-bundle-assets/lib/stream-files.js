var gulp = require('gulp'),
  through = require('through2'),
  gif = require('gulp-if'),
  concat = require('gulp-concat'),
  uglify = require('gulp-uglify'),
  rev = require('gulp-rev'),
  streamify = require('gulp-streamify'),
  minifyCSS = require('gulp-minify-css'),
  gsourcemaps = require('gulp-sourcemaps'),
  gutil = require('gulp-util'),
  order = require('gulp-order'),
  logger = require('./service/logger'),
  using = require('./using'),
  sourcemaps = require('./sourcemaps'),
  isOptionEnabled = require('./is-option-enabled'),
  addBundleResultsToFile = require('./results/add-bundle-results-to-file'),
  BundleKeys = require('./model/bundle-keys'),
  isMinEnabled = require('./is-min-enabled'),
  defaults = require('lodash').defaults;

module.exports.handleTransformError = function (thisStream, isWatch, bundleName, bundleKey, err) {
  logger.log(gutil.colors.red("ERROR in custom transforms for '" + bundleName + "." + bundleKey + "':"));
  logger.log(err.toString());
  if (isWatch) {
    thisStream.emit('end');
  } else {
    process.exit(1);
  }
};

module.exports.attachStreamOptions = function (file, opts) {
  // add some useful options to file for possible inspection later in external custom gulp streams
  file.bundleOptions = {
    env: opts.env,
    type: opts.type,
    bundleName: opts.bundleName,
    isWatch: opts.isWatch,
    isBundleAll: opts.isBundleAll
  };
};

module.exports.scripts = function (opts) {
  var self = this,
    concatOpts = defaults({
      path: opts.bundleName + ((opts.isBundleAll && opts.env) ? '.' + opts.env : '') + '.js'
    }, opts.bundleOptions.pluginOptions['gulp-concat']);

  return gulp.src(opts.src, {base: opts.base})
    .pipe(using.bundle(opts.bundleName, BundleKeys.SCRIPTS, opts.env, opts.isBundleAll))
    .pipe(through.obj(function (file, enc, cb) {
      self.attachStreamOptions(file, opts);
      this.push(file);
      cb();
    }))
    .pipe(gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, gsourcemaps.init(opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.SCRIPTS].init)
    ))
    .pipe(opts.bundleOptions.transforms[BundleKeys.SCRIPTS]())
    .on('error', function (e) {
      self.handleTransformError(this, opts.isWatch, opts.bundleName, BundleKeys.SCRIPTS, e);
    })
    .pipe(gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, through.obj(sourcemaps.verify)
    ))
    .pipe(gif(function (file) {
        return isMinEnabled.js(file, opts);
      },
      gif(function (file) {
          return file.isStream();
        },
        streamify(
          uglify(opts.bundleOptions.pluginOptions['gulp-uglify'])),
        uglify(opts.bundleOptions.pluginOptions['gulp-uglify'])
      )))
    .on('error', function (e) {
      self.handleTransformError(this, opts.isWatch, opts.bundleName, BundleKeys.SCRIPTS, e);
    })
    .pipe(gif(function (file) {
        return opts.bundleOptions.order && opts.bundleOptions.order.scripts;
      }, order(opts.bundleOptions.order.scripts)
    ))
    .pipe(gif(function (file) {
        return file.isStream();
      },
      streamify(concat(concatOpts)),
      concat(concatOpts)
    ))
    .pipe(gif(isOptionEnabled(opts.bundleOptions.rev, opts.env),
      gif(function (file) {
        return file.isStream();
      }, streamify(rev()), rev())
    ))
    .pipe(gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, gsourcemaps.write(
        opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.SCRIPTS].destPath,
        opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.SCRIPTS].write
      )
    ))
    .pipe(addBundleResultsToFile(opts.bundleName, BundleKeys.SCRIPTS, opts.bundleOptions.result, opts.env, opts.isBundleAll));
};

module.exports.styles = function (opts) {
  var self = this;
  return gulp.src(opts.src, {base: opts.base})
    .pipe(using.bundle(opts.bundleName, BundleKeys.STYLES, opts.env, opts.isBundleAll))
    .pipe(through.obj(function (file, enc, cb) {
      self.attachStreamOptions(file, opts);
      this.push(file);
      cb();
    }))
    .pipe(
    gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, gsourcemaps.init(opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.STYLES].init)
    ))
    .pipe(opts.bundleOptions.transforms[BundleKeys.STYLES]())
    .on('error', function (e) {
      self.handleTransformError(this, opts.isWatch, opts.bundleName, BundleKeys.STYLES, e);
    })
    .pipe(gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, through.obj(sourcemaps.verify)
    ))
    .pipe(gif(function (file) {
      return isMinEnabled.css(file, opts);
    }, minifyCSS(opts.bundleOptions.pluginOptions['gulp-minify-css'])))
    .pipe(gif(function (file) {
        return opts.bundleOptions.order && opts.bundleOptions.order.styles;
      }, order(opts.bundleOptions.order.styles)
    ))
    .pipe(concat(opts.bundleName + ((opts.isBundleAll && opts.env) ? '.' + opts.env : '') + '.css'))
    .pipe(gif(isOptionEnabled(opts.bundleOptions.rev, opts.env), rev()))
    .pipe(gif(function (file) {
        return sourcemaps.isEnabled(opts);
      }, gsourcemaps.write(
        opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.STYLES].destPath,
        opts.bundleOptions.pluginOptions['gulp-sourcemaps'][BundleKeys.STYLES].write
      )
    ))
    .pipe(addBundleResultsToFile(opts.bundleName, BundleKeys.STYLES, opts.bundleOptions.result, opts.env, opts.isBundleAll));
};