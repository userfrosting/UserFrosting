var gutil = require('gulp-util'),
  cache = require('./cache');

function Logger() {
}

Logger.prototype.log = function () {
  var config = cache.get('config');
  if (!config || !config.options || config.options.quietMode !== true) {
    // replace with gulp logger once it's done? https://github.com/gulpjs/gulp-util/issues/33
    gutil.log.apply(gutil.log, arguments);
  }
};

// naturally a singleton because node's require caches the value assigned to module.exports
module.exports = new Logger();