module.exports = Bundle;

function Bundle(options) {
  if (!options) options = {};
  this.name = options.name || null; // "main", "vendor", etc.
  this.type = options.type || null; // "scripts" | "styles"
  this.result = options.result || {};
  this.result.type = this.result.type || 'html'; // "jsx", "plain", etc
  this.env = options.env || ''; // current NODE_ENV
  this.bundleAllEnvironments = typeof options.bundleAllEnvironments !== 'undefined' ?
    options.bundleAllEnvironments : false; // flag from main bundle options
}