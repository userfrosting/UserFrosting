<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative panel management.
 */
$app->group('/dashboard', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:pageDashboard')
         ->setName('dashboard');
})->add('authGuard');

$app->group('/api/dashboard', function () {
    $this->post('/clear-cache', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:clearCache');
})->add('authGuard');

$app->group('/modals/dashboard', function () {
    $this->get('/clear-cache', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:getModalConfirmClearCache');
})->add('authGuard');
