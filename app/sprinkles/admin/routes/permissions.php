<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative permission management.
 */
$app->group('/admin/permissions', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:pageList')
        ->setName('uri_permissions');

    $this->get('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:pageInfo');
})->add('authGuard');

$app->group('/api/permissions', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getList');

    $this->get('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getInfo');
})->add('authGuard');
