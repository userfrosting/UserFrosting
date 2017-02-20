<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\ServicesProvider;

use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Registers services for the admin sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AdminServicesProvider
{
    /**
     * Register UserFrosting's admin services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extend the 'classMapper' service to register sprunje classes.
         *
         * Mappings added: 'activity_sprunje', 'group_sprunje', 'permission_sprunje', 'role_sprunje', 'user_sprunje'
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('activity_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje');
            $classMapper->setClassMapping('group_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\GroupSprunje');
            $classMapper->setClassMapping('permission_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\PermissionSprunje');
            $classMapper->setClassMapping('role_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje');
            $classMapper->setClassMapping('user_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje');
            return $classMapper;
        });

        /**
         * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
         */
        $container['determineRedirectOnLogin'] = function ($c) {
            return function ($response) use ($c)
            {
                /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
                $authorizer = $c->authorizer;

                $currentUser = $c->authenticator->user();

                if ($authorizer->checkAccess($currentUser, 'uri_dashboard')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('dashboard'));
                } elseif ($authorizer->checkAccess($currentUser, 'uri_account_settings')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('settings'));
                } else {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('index'));
                }
            };
        };
    }
}
