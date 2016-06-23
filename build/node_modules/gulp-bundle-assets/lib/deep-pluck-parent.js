var _ = require('lodash');

/**
 * http://stackoverflow.com/questions/15642494/find-property-by-name-in-a-deep-object
 * `this` should be the collection to be traversed
 * @param key {String}
 * @returns {Array} Returns a new array of objects with that property
 */
module.exports = function deepPluckParent(key) {
  if (_.has(this, key)) {
    return [this];
  }
  var res = [];
  _.forEach(this, function (v) {
    if (typeof v === "object" && (v = deepPluckParent.call(v, key)).length) {
      res.push.apply(res, v);
    }
  });
  return res;
};