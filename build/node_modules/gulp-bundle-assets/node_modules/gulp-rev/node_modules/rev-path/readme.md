# rev-path [![Build Status](https://travis-ci.org/sindresorhus/rev-path.svg?branch=master)](https://travis-ci.org/sindresorhus/rev-path)

> Create a [revved file path](http://blog.risingstack.com/automatic-cache-busting-for-your-css/)


## Install

```
$ npm install --save rev-path
```


## Usage

```js
var revPath = require('rev-path');
var hash = 'bb9d8fe615'

var path = revPath('src/unicorn.png', hash);
//=> 'src/unicorn-bb9d8fe615.png'

revPath('src/unicorn.png', Date.now());
//=> 'src/unicorn-1432309925925.png'

// you can also revert an already hashed path
revPath.revert(path, hash);
//=> 'src/unicorn.png'
```


## License

MIT © [Sindre Sorhus](http://sindresorhus.com)
