html4-id
=====
[![npm](https://img.shields.io/npm/v/html4-id.svg)](https://www.npmjs.com/package/html4-id)
[![Downloads](https://img.shields.io/npm/dm/html4-id.svg)](https://www.npmjs.com/package/html4-id)
[![Node.js Version](https://img.shields.io/node/v/html4-id.svg)](https://www.npmjs.com/package/html4-id)
[![Build Status](https://travis-ci.org/RiptideElements/html4-id.svg?branch=master)](https://travis-ci.org/RiptideElements/html4-id)
[![Coverage Status](https://coveralls.io/repos/github/RiptideElements/html4-id/badge.svg?branch=master)](https://coveralls.io/github/RiptideElements/html4-id?branch=master)
[![bitHound Dependencies](https://www.bithound.io/github/RiptideElements/html4-id/badges/dependencies.svg)](https://www.bithound.io/github/RiptideElements/html4-id/master/dependencies/npm)
[![bitHound Dev Dependencies](https://www.bithound.io/github/RiptideElements/html4-id/badges/devDependencies.svg)](https://www.bithound.io/github/RiptideElements/html4-id/master/dependencies/npm)
[![bitHound Code](https://www.bithound.io/github/RiptideElements/html4-id/badges/code.svg)](https://www.bithound.io/github/RiptideElements/html4-id)

Create a valid HTML4 ID from an input value.

## Install

#### NPM
```bash
$ npm install html4-id
```

## Node.js
```js
var idify = require('html4-id');

idify('This is a test!'); // Returns "this-is-a-test"
```

## Browser
```html
<script src="node_modules/html4-id/dist/html4-id.min.js"></script>

<div id="{{idify('This is a test!'}}"></div> <!-- Has the ID "this-is-a-test" -->
```

### idify(string)
Removes invalid characters and generates an id meant to be used for HTML elements. See [MDN](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/id) for the note about invalid characters.

## License
[MIT](LICENSE)