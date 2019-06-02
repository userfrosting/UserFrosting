<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use UserFrosting\Sprinkle\Core\Util\NoCache;

/*
 * Routes for administrative panel management.
 */
$app->group('/dashboard', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:pageDashboard')
         ->setName('dashboard');
})->add('authGuard')->add(new NoCache());

$app->group('/api/dashboard', function () {
    $this->post('/clear-cache', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:clearCache');
})->add('authGuard')->add(new NoCache());

$app->group('/modals/dashboard', function () {
    $this->get('/clear-cache', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:getModalConfirmClearCache');
})->add('authGuard')->add(new NoCache());
