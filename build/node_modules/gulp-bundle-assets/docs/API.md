# API

## bundle([options])

### options

Type: `Object`

Options to configure bundling.

### options.base

Type: `String`

Default: `.`

Base directory when resolving src globs. Useful when running gulp tasks from a `gulpfile` outside the project's root.

### options.bundleAllEnvironments

Type: `Boolean`

Default: `false`

When `true`, generates all bundles and bundle result jsons for all environments.
This will parse your `bundle.config.js` looking for all environment definitions.
See [this example](../examples/bundle-all-environments) to see the flag in action.

### options.quietMode

Type: `Boolean`

Default: `false`

Flag to disable all console logging.

## bundle.results([options])

Note: beyond this api, bundle results can be further modified with config options like
[custom result types](../examples/custom-result)

### options

Type: `Object` or `String`

If a `String` is passed, it represents the destination of the `bundle.result.json` file, e.g.

```js
gulp.task('bundle', function() {
  return gulp.src('./bundle.config.js')
    .pipe(bundle())
    .pipe(bundle.results('./')) // arg is destination of the result json file
    .pipe(gulp.dest('./public'));
});
```

will place `bundle.result.json` at the project root directory.

### options.dest

Type: `String`

Default: `./`

Same as just passing a `String` to `bundle.results()`. This is the destination of `bundle.result.json`

### options.pathPrefix

Type: `String`

Default: `''`

Appends a string to the beginning of each file path generated in `bundle.result.json`. Example usage:

```js
gulp.task('bundle', function () {
  return gulp.src('./bundle.config.js')
    .pipe(bundle())
    .pipe(bundle.results({
      pathPrefix: '/public/'
    }))
    .pipe(gulp.dest('./public'));
});
```

E.g., if the string was empty you may see a `bundle.result.json` like this:

```js
{
  "main": {
    "styles": "<link href='main.css' media='all' rel='stylesheet' type='text/css'/>",
    "scripts": "<script src='main.js' type='text/javascript'></script>"
  }
}
```

If you set `pathPrefix` to `/public/`, `bundle.result.json` would look like this:

```js
{
  "main": {
    "styles": "<link href='/public/main.css' media='all' rel='stylesheet' type='text/css'/>",
    "scripts": "<script src='/public/main.js' type='text/javascript'></script>"
  }
}
```

### options.fileName

Type: `String`

Default: `bundle.result`

Allows you to change the name of the resulting `bundle.result.json`. E.g.

```js
gulp.task('bundle', function () {
  return gulp.src('./bundle.config.js')
    .pipe(bundle())
    .pipe(bundle.results({
      fileName: 'manifest'
    }))
    .pipe(gulp.dest('./public'));
});
```

Would result in a file named `manifest.json` created at the project root.