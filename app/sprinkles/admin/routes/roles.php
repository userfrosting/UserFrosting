<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative role management.
 */
$app->group('/admin/roles', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageRoles')
        ->setName('uri_roles');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageRole');
});

$app->group('/api/roles', function () {
    $this->delete('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:deleteRole');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getRoles');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getRole');

    $this->get('/r/{slug}/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getRolePermissions');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:createRole');

    $this->put('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateRole');

    $this->put('/r/{slug}/{field}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateRoleField');
});

$app->group('/modals/roles', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalConfirmDeleteRole');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalCreateRole');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEditRole');

    $this->get('/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEditRolePermissions');
});
