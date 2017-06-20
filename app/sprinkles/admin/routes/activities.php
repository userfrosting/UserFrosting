<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative activity monitoring.
 */
$app->group('/activities', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\ActivityController:pageList')
        ->setName('uri_activities');
})->add('authGuard');

$app->group('/api/activities', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\ActivityController:getList');
})->add('authGuard');
