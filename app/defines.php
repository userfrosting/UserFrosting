<?php

namespace UserFrosting;

// Some standard defines
define('UserFrosting\VERSION', 'special');
define('UserFrosting\DS', '/');
define('UserFrosting\PHP_MIN', '5.4.0');
define('UserFrosting\DEBUG_CONFIG', false);

// Directories and Paths

// The directory in which the non-public files reside.  Should be the same as the directory that this file is in.
if (!defined('UserFrosting\APP_DIR')) {
    define('UserFrosting\APP_DIR', str_replace(DIRECTORY_SEPARATOR, DS, __DIR__));
}

// The directory containing APP_DIR.  Usually, this will contain the entire website.
define('UserFrosting\ROOT_DIR', realpath(__DIR__ . '/..'));

// The directory in which site-specific subdirectories reside.
define('UserFrosting\SITES_DIR', ROOT_DIR . '/sites');

// Composer's vendor directory
define('UserFrosting\VENDOR_DIR', APP_DIR . '/vendor');

define('UserFrosting\INIT_DIR_NAME', 'initialize');
define('UserFrosting\CONFIG_DIR_NAME', 'config');
define('UserFrosting\ROUTE_DIR_NAME', 'routes');
define('UserFrosting\TEMPLATE_DIR_NAME', 'templates/themes');
define('UserFrosting\SCHEMA_DIR_NAME', 'schema');
define('UserFrosting\LOCALE_DIR_NAME', 'locale');
define('UserFrosting\PLUGIN_DIR_NAME', 'plugins');
define('UserFrosting\LOG_DIR_NAME', 'logs');
