var _ = require('lodash');

/**
 * http://stackoverflow.com/questions/15642494/find-property-by-name-in-a-deep-object
 * `this` should be the collection to be traversed
 * @param key {String}
 * @returns {Array} Returns a new array of property values
 */
module.exports = function deepPluck(key) {
  if (_.has(this, key)) {
    return [this[key]];
  }
  var res = [];
  _.forEach(this, function (v) {
    if (typeof v === "object" && (v = deepPluck.call(v, key)).length) {
      res.push.apply(res, v);
    }
  });
  return res;
};