<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative panel management.
 */
$app->group('/admin', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\AdminController:dashboard')
        ->setName('uri_admin');
});