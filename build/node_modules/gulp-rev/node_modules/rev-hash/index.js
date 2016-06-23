'use strict';
var crypto = require('crypto');

module.exports = function (buf) {
	if (!Buffer.isBuffer(buf)) {
		throw new TypeError('Expected a buffer');
	}

	return crypto.createHash('md5').update(buf).digest('hex').slice(0, 10);
};
