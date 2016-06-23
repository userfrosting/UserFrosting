![status](https://secure.travis-ci.org/wearefractal/gulp-coffee.png?branch=master)

## Information

<table>
<tr>
<td>Package</td><td>gulp-coffee</td>
</tr>
<tr>
<td>Description</td>
<td>Compiles CoffeeScript</td>
</tr>
<tr>
<td>Node Version</td>
<td>>= 0.9</td>
</tr>
</table>

## Usage

```javascript
var coffee = require('gulp-coffee');

gulp.task('coffee', function() {
  gulp.src('./src/*.coffee')
    .pipe(coffee({bare: true}).on('error', gutil.log))
    .pipe(gulp.dest('./public/'))
});
```

### Error handling

gulp-coffee will emit an error for cases such as invalid coffeescript syntax. If uncaught, the error will crash gulp.

You will need to attach a listener (i.e. `.on('error')`) for the error event emitted by gulp-coffee:

```javascript
var coffeeStream = coffee({bare: true});

// Attach listener
coffeeStream.on('error', function(err) {});
```

In addition, you may utilize [gulp-util](https://github.com/wearefractal/gulp-util)'s logging function:

```javascript
var gutil = require('gulp-util');

// ...

var coffeeStream = coffee({bare: true});

// Attach listener
coffeeStream.on('error', gutil.log);

```

Since `.on(...)` returns `this`, you can make you can compact it as inline code:

```javascript

gulp.src('./src/*.coffee')
  .pipe(coffee({bare: true}).on('error', gutil.log))
  // ...
```

## Options

The options object supports the same options as the standard CoffeeScript compiler

## Source maps

gulp-coffee can be used in tandem with [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) to generate source maps for the coffee to javascript transition. You will need to initialize [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) prior to running the gulp-coffee compiler and write the source maps after.

```javascript
var sourcemaps = require('gulp-sourcemaps');

gulp.src('./src/*.coffee')
  .pipe(sourcemaps.init())
  .pipe(coffee())
  .pipe(sourcemaps.write())
  .pipe(gulp.dest('./dest/js'));

// will write the source maps inline in the compiled javascript files
```

By default, [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) writes the source maps inline in the compiled javascript files. To write them to a separate file, specify a relative file path in the `sourcemaps.write()` function.

```javascript
var sourcemaps = require('gulp-sourcemaps');

gulp.src('./src/*.coffee')
  .pipe(sourcemaps.init())
  .pipe(coffee({ bare: true })).on('error', gutil.log)
  .pipe(sourcemaps.write('./maps'))
  .pipe(gulp.dest('./dest/js'));

// will write the source maps to ./dest/js/maps
```

## LICENSE

(MIT License)

Copyright (c) 2015 Fractal <contact@wearefractal.com>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
