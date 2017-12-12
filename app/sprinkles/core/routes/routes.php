<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

global $app;
$config = $app->getContainer()->get('config');

$app->get('/', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageIndex')
    ->add('checkEnvironment')
    ->setName('index');

$app->get('/about','UserFrosting\Sprinkle\Core\Controller\CoreController:pageAbout')->add('checkEnvironment');

$app->get('/alerts', 'UserFrosting\Sprinkle\Core\Controller\CoreController:jsonAlerts');

$app->get('/legal', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pageLegal');

$app->get('/privacy', 'UserFrosting\Sprinkle\Core\Controller\CoreController:pagePrivacy');

$app->get('/' . $config['assets.raw.path'] . '/{url:.+}', 'UserFrosting\Assets\ServeAsset\SlimServeAsset:serveAsset');
