var path = require('path'),
  util = require('util'),
  _ = require('lodash'),
  ERROR_MSG_PREFIX = 'Config parse error. ';

function stringSrc(src, base) {
  if (base && base !== '.') {
    return path.join(base, src);
  }
  return src;
}

function arrayOrStringSrc(src, base, options, env) {
  if (typeof src === 'string') {
    return stringSrc(src, base);
  } else if (util.isArray(src)) {
    for (var i = 0; i < src.length; i++) {
      if (src[i] && typeof src[i] === 'object') { // typeof null === 'object'
        src[i] = objectSrc(src[i], base, options, env);
      } else if (typeof src[i] === 'string') {
        src[i] = stringSrc(src[i], base);
      } else {
        throw new Error(ERROR_MSG_PREFIX + 'Invalid bundle glob detected. Expected string or object but got ' + src);
      }
    }
    return src;
  }
  throw new Error(ERROR_MSG_PREFIX + 'Invalid bundle glob detected. Expected string or array but got ' + src);
}

function objectSrc(src, base, options, env) {
  if (options && !src.src && options.useMin === false) {
    throw new Error(ERROR_MSG_PREFIX + 'useMin=false but no src defined');
  } else if (!options) {
    if (!src.src) {
      if (!src.minSrc) {
        throw new Error(ERROR_MSG_PREFIX + 'Invalid bundle glob detected. Neither src nor minSrc defined.');
      }
      return arrayOrStringSrc(src.minSrc, base, options, env);
    }
  } else if (options.useMin) {
    if (typeof options.useMin === 'string') {
      if (options.useMin === env && src.minSrc) {
        return arrayOrStringSrc(src.minSrc, base, options, env);
      }
      return arrayOrStringSrc(src.src, base, options, env);
    } else if (util.isArray(options.useMin)) {
      if (_.contains(options.useMin, env) && src.minSrc) {
        return arrayOrStringSrc(src.minSrc, base, options, env);
      }
      return arrayOrStringSrc(src.src, base, options, env);
    } else if (!src.minSrc) {
      return arrayOrStringSrc(src.src, base, options, env); // for array notation allow some to be defined
    }
    return arrayOrStringSrc(src.minSrc, base, options, env);
  }
  return arrayOrStringSrc(src.src, base, options, env);
}

/**
 * Converts a config value of src glob(s) to a result that gulp can understand
 * https://github.com/wearefractal/vinyl-fs/blob/master/lib/src/index.js#L41
 *
 * @param {String|Array|Object} src
 * @param {String} base
 * @param {Object} options
 * @param {String} env
 * @returns {String|Array}
 */
module.exports = function (src, base, options, env) {
  var srcCopy = _.cloneDeep(src); // this func mutates src so make a copy first
  if (typeof srcCopy === 'string' || util.isArray(srcCopy)) {
    return arrayOrStringSrc(srcCopy, base, options, env);
  } else if (srcCopy && typeof srcCopy === 'object') { // typeof null === 'object'
    return objectSrc(srcCopy, base, options, env);
  }
  throw new Error(ERROR_MSG_PREFIX + 'Invalid bundle glob detected. Expected string, array or object but got ' + srcCopy);
};
