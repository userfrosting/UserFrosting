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
$app->group('/groups', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:pageGroups')
        ->setName('uri_groups');

    $this->get('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:pageGroup');

    $this->get('/g/{slug}/users', 'UserFrosting\Sprinkle\Admin\Controller\UserController:pageGroupUsers');
});

$app->group('/api/groups', function () {
    $this->delete('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:deleteGroup');

    $this->get('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:getGroup');

    $this->put('', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:createGroup');

    $this->post('/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:updateGroup');

    $this->get('/g/{slug}/users', 'UserFrosting\Sprinkle\Admin\Controller\UserController:getGroupUsers');
});

$app->group('/forms/groups', function () {
    $this->get('/create/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:formCreateGroup');

    $this->get('/edit/g/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\GroupController:formEditGroup');
});
