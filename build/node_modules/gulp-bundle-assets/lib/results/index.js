var through = require('through2'),
  gutil = require('gulp-util'),
  path = require('path'),
  bluebird = require('bluebird'),
  gracefulFs = bluebird.promisifyAll(require('graceful-fs')),
  fsExists = require('graceful-fs').exists,
  mkdirp = require('mkdirp'),
  _ = require('lodash'),
  addBundleResults = require('./add-to-results');

function defaulOptions(opts) {
  var options = {
    dest: './',
    pathPrefix: '',
    fileName: 'bundle.result'
  };
  if (typeof opts === 'string') {
    options.dest = opts;
  } else {
    _.assign(options, opts);
  }
  return options;
}

module.exports = {
  all: function (opts) {
    var resultJsons = {},
      options = defaulOptions(opts);

    function collectResults(file, enc, cb) {
      addBundleResults(resultJsons, file, options.pathPrefix, options.fileName);
      this.push(file);
      cb();
    }

    function writeResults(done) {
      mkdirp(options.dest, function (err) {
        if (err) throw err;

        var streams = [];

        _.each(resultJsons, function (result) {
          var filePath = path.join(options.dest, result.filename),
            data = JSON.stringify(result.contents, null, 2);
          streams.push(gracefulFs.writeFileAsync(filePath, data));
        });

        bluebird.all(streams).then(function () {
          done();
        });

      });
    }

    return through.obj(collectResults, writeResults);
  },
  incremental: function (opts) {
    var resultJsons = {},
      options = defaulOptions(opts);

    return through.obj(function (file, enc, cb) {

      var self = this;

      if (file.bundle) {

        addBundleResults(resultJsons, file, options.pathPrefix, options.fileName);

        mkdirp(options.dest, function (err) {
          if (err) throw err;

          var envKey = _.findKey(resultJsons, function () {
            return true;
          });

          var filePath = path.join(options.dest, resultJsons[envKey].filename);

          fsExists(filePath, function (exists) {

            var action;
            if (exists) {
              action = gracefulFs.readFileAsync(filePath, 'utf8')
                .then(function (data) {
                  var newData = resultJsons[envKey].contents;
                  var mergedData = _.merge(JSON.parse(data), newData);
                  return gracefulFs.writeFileAsync(filePath, JSON.stringify(mergedData, null, 2));
                });
            } else {
              var freshData = JSON.stringify(resultJsons[envKey].contents, null, 2);
              action = gracefulFs.writeFileAsync(filePath, freshData);
            }
            action.then(function () {
              self.push(file);
              cb();
            });
          });
        });

      } else {
        self.push(file);
        cb();
      }

    });
  }
};
