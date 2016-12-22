<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative user management.
 */
$app->group('/admin/users', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageUsers')
        ->setName('uri_users');

    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageUser');
});

$app->group('/api/users', function () {
    $this->delete('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:deleteUser');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getUsers');

    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getUser');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\UserController:createUser');

    $this->post('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:updateUser');
});

$app->group('/modals/users', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getModalConfirmDeleteUser');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getModalCreateUser');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getModalEditUser');

    $this->get('/password', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getModalEditUserPassword');
});
