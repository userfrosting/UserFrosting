function Cache() {
  this._cache = {};
}

Cache.prototype.set = function (key, value) {
  this._cache[key] = value;
};

Cache.prototype.get = function (key) {
  return this._cache[key];
};

// naturally a singleton because node's require caches the value assigned to module.exports
module.exports = new Cache();