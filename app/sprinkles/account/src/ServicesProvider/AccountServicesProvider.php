<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use Birke\Rememberme\Authenticator as RememberMe;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Twig\AccountExtension;

/**
 * Registers services for the account sprinkle, such as currentUser, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AccountServicesProvider
{
    /**
     * Register UserFrosting's account services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extends the 'errorHandler' service with custom exception handlers.
         *
         * Custom handlers added: ForbiddenExceptionHandler
         */
        $container->extend('errorHandler', function ($handler, $c) {
            // Register the ForbiddenExceptionHandler.
            $handler->registerHandler('\UserFrosting\Support\Exception\ForbiddenException', '\UserFrosting\Sprinkle\Account\Handler\ForbiddenExceptionHandler');
            
            return $handler;
        });
        
        /**
         * Extends the 'view' service with the AccountExtension for Twig.
         *
         * Adds account-specific functions, globals, filters, etc to Twig.
         */
        $container->extend('view', function ($view, $c) {
            $twig = $view->getEnvironment(); 
            $extension = new AccountExtension($c);
            $twig->addExtension($extension);
            
            return $view;
        });
        
        $container['authenticator'] = function ($c) {
            $config = $c->get('config');
            $session = $c->get('session');
            
            // Force database connection to boot up
            $c->get('db');            
            
            // Fix RememberMe table name
            $config['remember_me.table.tableName'] = Capsule::connection()->getTablePrefix() . $config['remember_me.table.tableName'];          
            
            $authenticator = new Authenticator($session, $config);
            return $authenticator;
        };
        
        /**
         * Loads the User object for the currently logged-in user.
         *
         * Tries to re-establish a session for "remember-me" users who have been logged out, or creates a guest user object if no one is logged in.
         * @todo Move some of this logic to the Authenticate class.
         */ 
        $container['currentUser'] = function ($c) {
            $authenticator = $c->get('authenticator');
            $config = $c->get('config');
            // Force database connection to boot up
            $c->get('db');
            
            // Now, check to see if we have a user in session or rememberMe cookie
            $currentUser = $authenticator->getSessionUser();
            
            // If no authenticated user, create a 'guest' user object
            if (!$currentUser) {
                $currentUser = new User();
                $currentUser->id = $config['reserved_user_ids.guest'];
            }
            
            // TODO: Add user locale in translator
            // TODO: Set user theme in Twig
            /*
            // Set path to user's theme, prioritizing over any other themes.
            $loader = $twig->getLoader();
            $loader->prependPath($this->config('themes.path') . "/" . $this->user->getTheme());
            */            
            
            return $currentUser;
        };
    }
}
