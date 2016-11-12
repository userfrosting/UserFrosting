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
$app->group('/roles', function () {
    $this->get('/', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageRoles')
        ->setName('uri_roles');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageRole');
});

$app->group('/api/roles', function () {
    $this->delete('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:deleteRole');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getRole');

    $this->put('/', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:createRole');

    $this->post('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateRole');
});
    
$app->group('/forms/roles', function () {
    $this->get('/create/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:formCreateRole');

    $this->get('/edit/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:formEditRole');
});
