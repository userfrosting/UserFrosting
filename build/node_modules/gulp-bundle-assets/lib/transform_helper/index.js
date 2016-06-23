var lazypipe = require('lazypipe'),
  readableStream = require('readable-stream'),
  through = require('through2'),
  duplexer = require('duplexer2'),
  gif = require('gulp-if'),
  less = require('gulp-less'),
  coffee = require('gulp-coffee');

module.exports = {
  browserify: function (func) {
    return lazypipe()
      .pipe(function () {
        var writable = new readableStream.Writable({objectMode: true});
        var readable = through.obj(function (file, enc, cb) { // noop
          this.push(file);
          cb();
        });

        writable._write = function _write(file, encoding, done) {
          func(file, readable);
          return done();
        };

        return duplexer(writable, readable);
      });
  },
  less: function (opts) {
    opts = opts || {};
    return lazypipe()
      .pipe(function () {
        return gif(isLessFile, less(opts));
      });
  },
  coffee: function (opts) {
    opts = opts || {};
    return lazypipe()
      .pipe(function () {
        return gif(isCoffeeFile, coffee(opts));
      });
  }
};

function stringEndsWith(str, suffix) {
  return str.indexOf(suffix, str.length - suffix.length) !== -1;
}
function isLessFile(file) {
  return stringEndsWith(file.relative, 'less');
}

function isCoffeeFile(file) {
  return stringEndsWith(file.relative, 'coffee');
}