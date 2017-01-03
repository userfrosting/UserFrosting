<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative group management.
 */
$app->group('/admin/groups', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:pageGroups')
        ->setName('uri_groups');

    $this->get('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:pageGroup');

    $this->get('/g/{slug}/users', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageGroupUsers');
});

$app->group('/api/groups', function () {
    $this->delete('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:deleteGroup');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getGroups');

    $this->get('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getGroup');

    $this->get('/g/{slug}/users', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getGroupUsers');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:createGroup');

    $this->put('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:updateGroup');
});

$app->group('/modals/groups', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getModalConfirmDeleteGroup');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getModalCreateGroup');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getModalEditGroup');
});
