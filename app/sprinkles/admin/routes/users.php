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
$app->group('/users', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageUsers')
        ->setName('uri_users');

    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageUser');
});

$app->group('/api/users', function () {
    $this->delete('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:deleteUser');

    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getUser');

    $this->put('/', 'UserFrosting\Sprinkle\Admin\Controller\UserController:createUser');

    $this->post('/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:updateUser');
});
    
$app->group('/forms/users', function () {
    $this->get('/create/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:formCreateUser');

    $this->get('/edit/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:formEditUser');

    $this->get('/password/u/{user_name}', 'UserFrosting\Sprinkle\Admin\Controller\UserController:formEditUserPassword');
});
