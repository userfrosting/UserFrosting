<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->get('/', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageIndex')->add('checkEnvironment');

$app->get('/about','UserFrosting\Sprinkle\Core\Controller\CoreController:pageAbout')->add('checkEnvironment');

$app->get('/alerts', 'UserFrosting\Sprinkle\Core\Controller\CoreController:jsonAlerts');
