<?php

namespace UserFrosting;

// Some standard defines
define('UserFrosting\VERSION', '4.0.25-alpha');
define('UserFrosting\DS', '/');
define('UserFrosting\PHP_MIN_VERSION', '5.6');
define('UserFrosting\DEBUG_CONFIG', false);

// Directories and Paths

// The directory in which the non-public files reside.  Should be the same as the directory that this file is in.
if (!defined('UserFrosting\APP_DIR')) {
    define('UserFrosting\APP_DIR', str_replace(DIRECTORY_SEPARATOR, DS, __DIR__));
}

// The directory containing APP_DIR.  Usually, this will contain the entire website.
define('UserFrosting\ROOT_DIR', realpath(__DIR__ . '/..'));

// Composer's vendor directory
define('UserFrosting\VENDOR_DIR', APP_DIR . '/vendor');

// Directory names
define('UserFrosting\APP_DIR_NAME', basename(__DIR__));
define('UserFrosting\ASSET_DIR_NAME', 'assets');
define('UserFrosting\BUILD_DIR_NAME', 'build');
define('UserFrosting\CACHE_DIR_NAME', 'cache');
define('UserFrosting\EXTRA_DIR_NAME', 'extra');
define('UserFrosting\SESSION_DIR_NAME', 'sessions');
define('UserFrosting\CONFIG_DIR_NAME', 'config');
define('UserFrosting\LOCALE_DIR_NAME', 'locale');
define('UserFrosting\LOG_DIR_NAME', 'logs');
define('UserFrosting\ROUTE_DIR_NAME', 'routes');
define('UserFrosting\SCHEMA_DIR_NAME', 'schema');
define('UserFrosting\SRC_DIR_NAME', 'src');
define('UserFrosting\SPRINKLES_DIR_NAME', 'sprinkles');
define('UserFrosting\TEMPLATE_DIR_NAME', 'templates');
define('UserFrosting\PUBLIC_DIR_NAME', 'public');

// Vendor asset directories
define('UserFrosting\NPM_ASSET_DIR', APP_DIR_NAME . DS . ASSET_DIR_NAME . DS . 'node_modules');
define('UserFrosting\BOWER_ASSET_DIR', APP_DIR_NAME . DS . ASSET_DIR_NAME . DS . 'bower_components');