var _ = require('lodash');

module.exports = function (config, bundleFunc) {
  var streams = [],
    environments = config.getAllEnvironments();
  environments.push(''); // also run bundling with no env set

  _.forEach(environments, function (env) {
    streams = streams.concat(bundleFunc(config, env));
  });

  return streams;
};
