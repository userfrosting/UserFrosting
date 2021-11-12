// ------ jquery and bootstrap basics ------
// create global $ and jQuery variables
const $ = require('jquery');
global.$ = global.jQuery = $;

require('jquery-ui');
require('bootstrap-sass');
require('jquery-slimscroll');
require('bootstrap-select');

const Moment = require('moment');
global.moment = Moment;
require('daterangepicker');

// ------ AdminLTE framework ------
require('./admin-lte.scss');
require('admin-lte/dist/css/AdminLTE.min.css');
require('admin-lte/dist/css/skins/_all-skins.css');
require('./admin-lte-extensions.scss');
require('../userfrosting/css/userfrosting.css');

global.$.AdminLTE = {};
global.$.AdminLTE.options = {};
require('admin-lte/dist/js/adminlte.min');

// ------ Theme itself ------
// require('./default_avatar.png');

// ------ icheck for enhanced radio buttons and checkboxes ------
require('icheck');
require('icheck/skins/square/blue.css');