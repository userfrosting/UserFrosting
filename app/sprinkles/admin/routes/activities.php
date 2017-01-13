<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative activity monitoring.
 */
$app->group('/admin/activities', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\ActivityController:pageList')
        ->setName('uri_activities');
});

$app->group('/api/activities', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\ActivityController:getList');
});
