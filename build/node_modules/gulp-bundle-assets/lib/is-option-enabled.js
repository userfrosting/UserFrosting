var util = require('util');
var contains = require('lodash').contains;
module.exports = function(opt, env) {
  if (typeof opt === 'undefined') {
    return true;
  } else if (util.isArray(opt)) {
    return contains(opt, env);
  } else if (typeof opt === 'string') {
    return opt === env;
  }
  return !!opt;
};