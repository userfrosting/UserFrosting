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
 * Routes for administrative role management.
 */
$app->group('/roles', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageList')
        ->setName('uri_roles');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageInfo');
})->add('authGuard')->add(new NoCache());

$app->group('/api/roles', function () {
    $this->delete('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:delete');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getList');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getInfo');

    $this->get('/r/{slug}/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getPermissions');

    $this->get('/r/{slug}/users', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getUsers');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:create');

    $this->put('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateInfo');

    $this->put('/r/{slug}/{field}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateField');
})->add('authGuard')->add(new NoCache());

$app->group('/modals/roles', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalConfirmDelete');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalCreate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEdit');

    $this->get('/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEditPermissions');
})->add('authGuard')->add(new NoCache());
