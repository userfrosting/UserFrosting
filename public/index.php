<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Entry point for the /public site.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */

// First off, we'll grab the Composer dependencies
require_once __DIR__ . '/../app/vendor/autoload.php';

use UserFrosting\System\UserFrosting;

$uf = new UserFrosting();

$uf->run();
