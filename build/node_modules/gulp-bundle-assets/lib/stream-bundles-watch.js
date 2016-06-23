var through = require('through2'),
  BundleKeys = require('./model/bundle-keys'),
  gulp = require('gulp'),
  util = require('util'),
  using = require('./using'),
  sourcemaps = require('gulp-sourcemaps'),
  gutil = require('gulp-util'),
  logger = require('./service/logger'),
  gif = require('gulp-if'),
  _ = require('lodash'),
  pathifySrc = require('./pathify-config-src'),
  stringHelper = require('./string-helper'),
  bundleAllEnvironments = require('./bundle-all-environments'),
  bundleDone = require('./watch/bundle-done'),
  initOptionDefaults = require('./init-option-defaults'),
  results = require('./results').incremental,
  streamFiles = require('./stream-files'),
  streamCopy = require('./stream-copy'),
  streamBundlesUtil = require('./stream-bundles-util');

function _bundle(config, env) {
  var bundles = config.bundle,
    isBundleAll = config.options && config.options.bundleAllEnvironments,
    base = (config.options) ? config.options.base : '.', // can guarantee !!options b/c (config instanceof Config)
    resultOpts = (config.options) ? config.options.results : null,
    minSrcs = (config.getAllMinSrcs) ? config.getAllMinSrcs() : {};

  if (env) {
    logger.log('Creating bundle(s) for environment "' + env + '"');
  }

  _.forEach(Object.keys(bundles), function (bundleName) {

    var namedBundleObj = bundles[bundleName];
    initOptionDefaults(namedBundleObj);

    _.forEach(Object.keys(namedBundleObj), function (type) {
      var scriptWatch,
        styleWatch,
        scriptsPath,
        stylesPath,
        prettyScriptsBundleName,
        prettyStylesBundleName;

      /* jshint -W035 */
      if (type === BundleKeys.SCRIPTS) {

        scriptWatch = namedBundleObj[BundleKeys.OPTIONS].watch[BundleKeys.SCRIPTS];

        if (scriptWatch !== false) {

          scriptsPath = pathifySrc(namedBundleObj[BundleKeys.SCRIPTS], base, namedBundleObj[BundleKeys.OPTIONS], env);
          prettyScriptsBundleName = using.bundleName(bundleName, BundleKeys.SCRIPTS, env, isBundleAll);

          logger.log("Starting '" + gutil.colors.cyan("watch") + "' for bundle '" + gutil.colors.green(prettyScriptsBundleName) + "'...");

          gulp.watch((typeof scriptWatch === 'string' || util.isArray(scriptWatch)) ? scriptWatch : scriptsPath)
            .on('change', function (file) { // log changed file?

              var start = process.hrtime();

              streamFiles.scripts({
                src: scriptsPath,
                base: base,
                env: env,
                type: type,
                bundleName: bundleName,
                bundleOptions: namedBundleObj[BundleKeys.OPTIONS],
                isBundleAll: isBundleAll,
                minSrcs: minSrcs,
                isWatch: true
              })
                .pipe(gif(resultOpts, results(resultOpts)))
                .pipe(gulp.dest(config.options.dest))
                .pipe(through.obj(function (file, enc, cb) {
                  bundleDone(prettyScriptsBundleName, start);
                }));

            });
        }

      } else if (type === BundleKeys.STYLES) {

        styleWatch = namedBundleObj[BundleKeys.OPTIONS].watch[BundleKeys.STYLES];

        if (styleWatch !== false) {

          stylesPath = pathifySrc(namedBundleObj[BundleKeys.STYLES], base, namedBundleObj[BundleKeys.OPTIONS], env);
          prettyStylesBundleName = using.bundleName(bundleName, BundleKeys.STYLES, env, isBundleAll);

          logger.log("Starting '" + gutil.colors.cyan("watch") + "' for bundle '" + gutil.colors.green(prettyStylesBundleName) + "'...");

          gulp.watch((typeof styleWatch === 'string' || util.isArray(styleWatch)) ? styleWatch : stylesPath)
            .on('change', function (file) { // log changed file?

              var start = process.hrtime();

              streamFiles.styles({
                src: stylesPath,
                base: base,
                env: env,
                type: type,
                bundleName: bundleName,
                bundleOptions: namedBundleObj[BundleKeys.OPTIONS],
                isBundleAll: isBundleAll,
                minSrcs: minSrcs,
                isWatch: true
              })
                .pipe(gif(resultOpts, results(resultOpts)))
                .pipe(gulp.dest(config.options.dest))
                .pipe(through.obj(function (file, enc, cb) {
                  bundleDone(prettyStylesBundleName, start);
                }));

            });
        }

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

}

function watchStringCopyStream(config, item, base) {
  gulp.watch(item)
    .on('change', function (file) { // log changed file?
      streamCopy.getStringCopyStream(item, base)
        .pipe(gulp.dest(config.options.dest));
    });
}

function watchObjectCopyStream(config, item, base) {
  var watchPath = pathifySrc(item.src, base);
  gulp.watch(watchPath)
    .on('change', function (file) { // log changed file?
      streamCopy.getObjectCopyStream(item, base)
        .pipe(gulp.dest(config.options.dest));
    });
}

function _copy(config) {
  var base = (config.options) ? config.options.base : '.'; // can guarantee !!options b/c (config instanceof Config)

  logger.log("Starting '" + gutil.colors.cyan("watch") + "' for files to copy...");

  if (typeof config.copy === 'string') {
    watchStringCopyStream(config, config.copy, base);
  } else if (util.isArray(config.copy)) {
    _.forEach(config.copy, function (item) {
      if (typeof item === 'string') {
        return watchStringCopyStream(config, item, base);
      } else if (typeof item === 'object' && !util.isArray(item) &&
        config.copy.watch !== false) {
        return watchObjectCopyStream(config, item, base);
      }
      streamCopy.throwUnsupportedSyntaxError();
    });
  } else if (typeof config.copy === 'object' && config.copy.watch !== false) {
    watchObjectCopyStream(config, config.copy, base);
  } else {
    streamCopy.throwUnsupportedSyntaxError();
  }
}

function bundle(config) {

  if (config.bundle) {
    if (config.options && config.options.bundleAllEnvironments) { // can guarantee !!options b/c (config instanceof Config)
      bundleAllEnvironments(config, _bundle);
    } else {
      _bundle(config, process.env.NODE_ENV);
    }
  }

  streamBundlesUtil.warnIfNoBundleProperty(config);

  if (config.copy) {
    _copy(config);
  }
}

module.exports = bundle;