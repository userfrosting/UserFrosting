<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use UserFrosting\Sprinkle\Core\Util\NoCache;

global $app;
$config = $app->getContainer()->get('config');

$app->get('/', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageIndex')
    ->add('checkEnvironment')
    ->setName('index');

$app->get('/about', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageAbout')->add('checkEnvironment');

$app->get('/alerts', 'UserFrosting\Sprinkle\Core\Controller\CoreController:jsonAlerts')
    ->add(new NoCache());

$app->get('/legal', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageLegal');

$app->get('/privacy', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pagePrivacy');

$app->get('/' . $config['assets.raw.path'] . '/{url:.+}', 'UserFrosting\Sprinkle\Core\Controller\CoreController:getAsset');
