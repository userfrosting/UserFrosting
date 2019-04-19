<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting;

// Some standard defines
define('UserFrosting\VERSION', '4.2.1');
define('UserFrosting\DS', '/');
define('UserFrosting\PHP_MIN_VERSION', '5.6');
define('UserFrosting\PHP_RECOMMENDED_VERSION', '7.1');
define('UserFrosting\NODE_MIN_VERSION', 'v10.12.0');
define('UserFrosting\NPM_MIN_VERSION', '6.0.0');

// Directories and Paths

// The directory in which the non-public files reside.  Should be the same as the directory that this file is in.
if (!defined('UserFrosting\APP_DIR')) {
    define('UserFrosting\APP_DIR', str_replace(DIRECTORY_SEPARATOR, DS, __DIR__));
}

// The directory containing APP_DIR.  Usually, this will contain the entire website.
define('UserFrosting\ROOT_DIR', realpath(__DIR__ . '/..'));

// Names of app directories
define('UserFrosting\APP_DIR_NAME', basename(__DIR__));
define('UserFrosting\SPRINKLES_DIR_NAME', 'sprinkles');

// Names of src directories within Sprinkles
define('UserFrosting\SRC_DIR_NAME', 'src');

// Full path to Sprinkles directory
define('UserFrosting\SPRINKLES_DIR', APP_DIR . DS . SPRINKLES_DIR_NAME);

// Full path to sprinkles schema file
define('UserFrosting\SPRINKLES_SCHEMA_FILE', APP_DIR . DS . 'sprinkles.json');

// Full path to system Bakery commands
define('UserFrosting\BAKERY_SYSTEM_DIR', APP_DIR . DS . 'system' . DS . 'Bakery' . DS . 'Command');

// Relative path from within sprinkle directory
define('UserFrosting\BAKERY_DIR', SRC_DIR_NAME . DS . 'Bakery');
