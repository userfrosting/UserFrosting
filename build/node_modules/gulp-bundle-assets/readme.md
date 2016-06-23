# [gulp](http://gulpjs.com/)-bundle-assets [![NPM version][npm-image]][npm-url] [![Build Status][travis-image]][travis-url] [![Coverage Status][coverage-image]][coverage-url]

> Create static asset bundles from a config file: a common interface to combining, minifying, revisioning and more. Stack agnostic. Production ready.

By default uses the following gulp modules under the covers when creating bundles:

1. [gulp-concat](https://github.com/wearefractal/gulp-concat)
2. [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps)
3. [gulp-uglify](https://github.com/terinjokes/gulp-uglify)
4. [gulp-minify-css](https://github.com/jonathanepollack/gulp-minify-css)
6. [gulp-rev](https://github.com/sindresorhus/gulp-rev)
7. [gulp-order](https://github.com/sirlantis/gulp-order)

This project's stream architecture also allows you to plugin [any gulp transform you wish](examples/custom-transforms).

## Install

```bash
$ npm install gulp-bundle-assets --save-dev
```

## Basic Usage

Create the following files:

```js
// gulpfile.js
var gulp = require('gulp'),
  bundle = require('gulp-bundle-assets');

gulp.task('bundle', function() {
  return gulp.src('./bundle.config.js')
    .pipe(bundle())
    .pipe(gulp.dest('./public'));
});
```

```js
// bundle.config.js
module.exports = {
  bundle: {
    main: {
      scripts: [
        './content/js/foo.js',
        './content/js/baz.js'
      ],
      styles: './content/**/*.css'
    },
    vendor: {
      scripts: './bower_components/angular/angular.js'
    }
  },
  copy: './content/**/*.{png,svg}'
};
```

Then, calling

```bash
$ gulp bundle
```

Will result in the following folder structure:

```
-- public
   |-- content
   |   |-- fonts
   |   |-- images
   `main-8e6d79da08.css
   `main-5f17cd21a6.js
   `vendor-d66b96f539.js
```

## Advanced Usage

See [the examples folder](examples) for many other config options. The [full example](examples/full) shows most
all available options.

Also check out our [api docs](docs/API.md).

## Integrating bundles into your app

You can programmatically render your bundles into your view via
[your favorite templating engine](https://www.google.com/webhp?ion=1&espv=2&ie=UTF-8#q=node%20js%20templating%20engine)
and the resulting `bundle.result.json` file. To generate the `bundle.result.json`, add a call to `bundle.results`:

```js
// gulpfile.js
var gulp = require('gulp'),
  bundle = require('gulp-bundle-assets');

gulp.task('bundle', function() {
  return gulp.src('./bundle.config.js')
    .pipe(bundle())
    .pipe(bundle.results('./')) // arg is destination of bundle.result.json
    .pipe(gulp.dest('./public'));
});
```

Which results in a `bundle.result.json` file similar to:

```json
{
  "main": {
    "styles": "<link href='main-8e6d79da08.css' media='screen' rel='stylesheet' type='text/css'/>",
    "scripts": "<script src='main-5f17cd21a6.js' type='text/javascript'></script>"
  },
  "vendor": {
    "scripts": "<script src='vendor-d66b96f539.js' type='text/javascript'></script>",
    "styles": "<link href='vendor-23d5c9c6d1.css' media='screen' rel='stylesheet' type='text/css'/>"
  }
}
```

[See here for a full example using hogan](examples/express-app-using-result-json)

## Other Features

1. [watch src files and only build specific bundles](examples/full/gulpfile.js)
    * Greatly speeds up development
    * e.g. split out vendor and custom js files into different bundles so the custom bundle continues to build quickly on src change
2. [different bundles for different environments](examples/per-environment)
    * e.g. `NODE_ENV=production gulp bundle` could produce a set of bundles with minified src while just `gulp bundle` would have unminified src  
3. [custom gulp transforms](examples/custom-transforms/readme.md)
    * e.g. use `gulp-less`, `gulp-sass`, `gulp-coffee`, etc to further transform your files
4. [consume pre-minified src files](examples/full)
    * e.g. use jquery.min.js in production and jquery.js in dev
5. [custom result types](examples/custom-result)
    * e.g. create a bundle.result.json for html, jsx or any custom results you can think of
6. [works alongside 3rd party transformers](examples/browserify)
    * e.g. create a bundle using [browserify](http://browserify.org/), [6to5](https://github.com/sebmck/6to5), etc.
7. [guarantee bundle content order](examples/guarantee-content-order)
8. [modify built-in behavior with custom gulp plugin options](examples/full/bundle.config.js#L86)
9. [and much more!](examples/full/bundle.config.js)

## Why?

There are a number of ways to bundle static assets for use in your webapp.
Take for example:
[lumbar](http://walmartlabs.github.io/lumbar/),
[brunch](https://github.com/brunch/brunch),
[webpack](http://webpack.github.io/),
[browserify](http://browserify.org/),
[optimizer](https://github.com/raptorjs/optimizer),
[cartero](https://github.com/rotundasoftware/cartero),
[assetify](https://github.com/bevacqua/node-assetify),
[assets-packager](https://github.com/jakubpawlowicz/assets-packager), or
simply a mashup of custom [grunt](http://gruntjs.com/) or
[gulp](http://gulpjs.com/) plugins. All of these approaches are good in their
own way but none of them did everything we needed:

* handle all file types: js, css, less, sass, coffeescript, images, fonts, etc...
* handle a variety of js managers: amd, requirejs, etc...
* support common transforms: compression, minification, revisioning
* support custom transforms, e.g. [browserify](http://browserify.org/)
* logic must be common across webapps. That is, no copy/pasting of tasks. This
disqualified straight gulp or grunt.
* work with existing community plugins, namely [gulp](http://gulpjs.com/) tasks
* work with src from multiple locations, e.g. bower_components, node_modules, etc
* fast!

`gulp-bundle-assets` accomplishes all these goals and more. A main guiding
principle behind this project is to provide all necessary bundling functionality
while still being as flexible and customizable as possible.

## Changelog

* 2015/09/16 - v2.23.0 - add plugin option to modify built-in sourcemaps [#64](https://github.com/dowjones/gulp-bundle-assets/issues/64)
* 2015/07/17 - v2.22.0 - add config option for consistent file content ordering [#25](https://github.com/dowjones/gulp-bundle-assets/issues/25)
* 2015/06/11 - v2.21.0 - update all deps, including: `gulp-rev` 4.0.0, `gulp-less` 3.0.3, `gulp-sourcemaps` 1.5.2
* 2015/05/07 - v2.20.0 - add pluginOptions config option [#50](https://github.com/dowjones/gulp-bundle-assets/issues/50)
* 2015/05/07 - v2.19.2 - update to `gulp-minify-css` 1.1.1 ([@ZaleskiR](https://github.com/ZaleskiR))
* 2015/04/24 - v2.19.1 - fix `result.json` url separator on windows [#52](https://github.com/dowjones/gulp-bundle-assets/pull/52) ([@gregorymaertens](https://github.com/gregorymaertens))
* 2015/03/01 - v2.19.0 - fix error handling for `bundle.watch` [#47](https://github.com/dowjones/gulp-bundle-assets/pull/47)
* 2015/02/08 - v2.18.0 - add flag to disabled sourcemaps [#45](https://github.com/dowjones/gulp-bundle-assets/pull/45) ([@21brains-zh](https://github.com/21brains-zh))
* 2015/02/04 - v2.17.5 - update examples
* 2015/02/04 - v2.17.4 - add logging for errors from custom transforms [#41](https://github.com/dowjones/gulp-bundle-assets/issues/41)
* 2015/02/03 - v2.17.3 - update examples
* 2015/02/03 - v2.17.2 - add logging of bundle config parse errors
* 2014/12/05 - v2.17.1 - fix custom result file name during `bundle.watch` ([@roberto](https://github.com/roberto))
* 2014/12/05 - v2.17.0 - add custom result file name [#36](https://github.com/dowjones/gulp-bundle-assets/issues/36) ([@roberto](https://github.com/roberto))
* 2014/12/04 - v2.16.1 - fix tests
* 2014/12/01 - v2.16.0 - update deps, including: `gulp-rev` 2.0.1, `gulp-sourcemaps` 1.2.8, `gulp-uglify` 1.0.1
* 2014/10/21 - v2.15.2 - add support for both minCSS and minCss [#34](https://github.com/dowjones/gulp-bundle-assets/issues/34)
* 2014/10/10 - v2.15.1 - add example using 6to5 (aka [babel](https://babeljs.io/))
* 2014/09/29 - v2.15.0 - add `bundle.watch` for copy files [#33](https://github.com/dowjones/gulp-bundle-assets/issues/33)
* 2014/09/29 - v2.14.0 - add flag to disable logging [#16](https://github.com/dowjones/gulp-bundle-assets/issues/16)
* 2014/09/25 - v2.13.1 - fix when bundleAllEnvironments: true, srcMin is always true [#32](https://github.com/dowjones/gulp-bundle-assets/issues/32)
* 2014/09/23 - v2.13.0 - add to allow different watch vs bundle targets [#30](https://github.com/dowjones/gulp-bundle-assets/issues/30)
* 2014/09/23 - v2.12.1 - fix to only publish results during watch when opts defined
* 2014/09/23 - v2.12.0 - add `bundle.watch` for bundles [#26](https://github.com/dowjones/gulp-bundle-assets/issues/26)

## License

[MIT](LICENSE)

[npm-url]: https://npmjs.org/package/gulp-bundle-assets
[npm-image]: http://img.shields.io/npm/v/gulp-bundle-assets.svg
[travis-image]: https://travis-ci.org/dowjones/gulp-bundle-assets.svg?branch=master
[travis-url]: https://travis-ci.org/dowjones/gulp-bundle-assets
[coverage-image]: https://img.shields.io/coveralls/chmontgomery/gulp-bundle-assets.svg
[coverage-url]: https://coveralls.io/r/chmontgomery/gulp-bundle-assets
