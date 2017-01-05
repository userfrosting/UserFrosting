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
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:pagePermissions')
        ->setName('uri_permissions');

    $this->get('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:pagePermission');
});

$app->group('/api/permissions', function () {
    $this->delete('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:deletePermission');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getPermissions');

    $this->get('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getPermission');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:createPermission');

    $this->put('/p/{id}', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:updatePermission');
});

$app->group('/modals/permissions', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getModalConfirmDeletePermission');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getModalCreatePermission');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\PermissionController:getModalEditPermission');
});
