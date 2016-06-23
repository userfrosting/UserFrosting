module.exports = {
  endsWith: function (str, suffix) {
    if (typeof str !== 'string' || typeof suffix !== 'string') {
      throw new Error('stringHelper.endsWith expected strings but got str "' + str + '" and suffix "' + suffix + '"');
    }
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
  }
};