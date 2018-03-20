<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use Birke\Rememberme\Authenticator as RememberMe;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Account\Authenticate\Hasher;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler;
use UserFrosting\Sprinkle\Account\Log\UserActivityProcessor;
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Account\Repository\VerificationRepository;
use UserFrosting\Sprinkle\Account\Twig\AccountExtension;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;

/**
 * Registers services for the account sprinkle, such as currentUser, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's account services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extend the asset manager service to see assets for the current user's theme.
         */
        $container->extend('assets', function ($assets, $c) {

            // Register paths for user theme, if a user is logged in
            // We catch any authorization-related exceptions, so that error pages can be rendered.
            try {
                /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
                $authenticator = $c->authenticator;
                $currentUser = $c->currentUser;
            } catch (\Exception $e) {
                return $assets;
            }

            if ($authenticator->check()) {
                $c->sprinkleManager->addResource('assets', $currentUser->theme);
            }

            return $assets;
        });

        /**
         * Extend the 'classMapper' service to register model classes.
         *
         * Mappings added: User, Group, Role, Permission, Activity, PasswordReset, Verification
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Account\Database\Models\User');
            $classMapper->setClassMapping('group', 'UserFrosting\Sprinkle\Account\Database\Models\Group');
            $classMapper->setClassMapping('role', 'UserFrosting\Sprinkle\Account\Database\Models\Role');
            $classMapper->setClassMapping('permission', 'UserFrosting\Sprinkle\Account\Database\Models\Permission');
            $classMapper->setClassMapping('activity', 'UserFrosting\Sprinkle\Account\Database\Models\Activity');
            $classMapper->setClassMapping('password_reset', 'UserFrosting\Sprinkle\Account\Database\Models\PasswordReset');
            $classMapper->setClassMapping('verification', 'UserFrosting\Sprinkle\Account\Database\Models\Verification');
            return $classMapper;
        });

        /**
         * Extends the 'errorHandler' service with custom exception handlers.
         *
         * Custom handlers added: ForbiddenExceptionHandler
         */
        $container->extend('errorHandler', function ($handler, $c) {
            // Register the ForbiddenExceptionHandler.
            $handler->registerHandler('\UserFrosting\Support\Exception\ForbiddenException', '\UserFrosting\Sprinkle\Account\Error\Handler\ForbiddenExceptionHandler');
            // Register the AuthExpiredExceptionHandler
            $handler->registerHandler('\UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthExpiredException', '\UserFrosting\Sprinkle\Account\Error\Handler\AuthExpiredExceptionHandler');
            // Register the AuthCompromisedExceptionHandler.
            $handler->registerHandler('\UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthCompromisedException', '\UserFrosting\Sprinkle\Account\Error\Handler\AuthCompromisedExceptionHandler');
            return $handler;
        });

        /**
         * Extends the 'localePathBuilder' service, adding any locale files from the user theme.
         *
         */
        $container->extend('localePathBuilder', function ($pathBuilder, $c) {
            // Add paths for user theme, if a user is logged in
            // We catch any authorization-related exceptions, so that error pages can be rendered.
            try {
                /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
                $authenticator = $c->authenticator;
                $currentUser = $c->currentUser;
            } catch (\Exception $e) {
                return $pathBuilder;
            }

            if ($authenticator->check()) {
                // Add paths to locale files for user theme
                $themePath = $c->sprinkleManager->addResource('locale', $currentUser->theme);

                // Add user locale
                $pathBuilder->addLocales($currentUser->locale);
            }

            return $pathBuilder;
        });

        /**
         * Extends the 'view' service with the AccountExtension for Twig.
         *
         * Adds account-specific functions, globals, filters, etc to Twig, and the path to templates for the user theme.
         */
        $container->extend('view', function ($view, $c) {
            $twig = $view->getEnvironment();
            $extension = new AccountExtension($c);
            $twig->addExtension($extension);

            // Add paths for user theme, if a user is logged in
            // We catch any authorization-related exceptions, so that error pages can be rendered.
            try {
                /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
                $authenticator = $c->authenticator;
                $currentUser = $c->currentUser;
            } catch (\Exception $e) {
                return $view;
            }

            if ($authenticator->check()) {
                $theme = $currentUser->theme;
                $themePath = $c->sprinkleManager->addResource('templates', $theme);
                if ($themePath) {
                    $loader = $twig->getLoader();
                    $loader->prependPath($themePath);
                    // Add namespaced path as well
                    $loader->addPath($themePath, $theme);
                }
            }

            return $view;
        });

        /**
         * Authentication service.
         *
         * Supports logging in users, remembering their sessions, etc.
         */
        $container['authenticator'] = function ($c) {
            $classMapper = $c->classMapper;
            $config = $c->config;
            $session = $c->session;
            $cache = $c->cache;

            // Force database connection to boot up
            $c->db;

            // Fix RememberMe table name
            $config['remember_me.table.tableName'] = Capsule::connection()->getTablePrefix() . $config['remember_me.table.tableName'];

            $authenticator = new Authenticator($classMapper, $session, $config, $cache);
            return $authenticator;
        };

        /**
         * Sets up the AuthGuard middleware, used to limit access to authenticated users for certain routes.
         */
        $container['authGuard'] = function ($c) {
            $authenticator = $c->authenticator;
            return new AuthGuard($authenticator);
        };

        /**
         * Authorization check logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'auth' log stack.
         */
        $container['authLogger'] = function ($c) {
            $logger = new Logger('auth');

            $logFile = $c->get('locator')->findResource('log://userfrosting.log', true, true);

            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        /**
         * Authorization service.
         *
         * Determines permissions for user actions.  Extend this service to add additional access condition callbacks.
         */
        $container['authorizer'] = function ($c) {
            $config = $c->config;

            // Default access condition callbacks.  Add more in your sprinkle by using $container->extend(...)
            $callbacks = [
                /**
                 * Unconditionally grant permission - use carefully!
                 * @return bool returns true no matter what.
                 */
                'always' => function () {
                    return true;
                },

                /**
                 * Check if the specified values are identical to one another (strict comparison).
                 * @param mixed $val1 the first value to compare.
                 * @param mixed $val2 the second value to compare.
                 * @return bool true if the values are strictly equal, false otherwise.
                 */
                'equals' => function ($val1, $val2) {
                    return ($val1 === $val2);
                },

                /**
                 * Check if the specified values are numeric, and if so, if they are equal to each other.
                 * @param mixed $val1 the first value to compare.
                 * @param mixed $val2 the second value to compare.
                 * @return bool true if the values are numeric and equal, false otherwise.
                 */
                'equals_num' => function ($val1, $val2) {
                    if (!is_numeric($val1)) {
                        return false;
                    }
                    if (!is_numeric($val2)) {
                        return false;
                    }

                    return ($val1 == $val2);
                },

                /**
                 * Check if the specified user (by user_id) has a particular role.
                 *
                 * @param int $user_id the id of the user.
                 * @param int $role_id the id of the role.
                 * @return bool true if the user has the role, false otherwise.
                 */
                'has_role' => function ($user_id, $role_id) {
                    return Capsule::table('role_users')
                        ->where('user_id', $user_id)
                        ->where('role_id', $role_id)
                        ->count() > 0;
                },

                /**
                 * Check if the specified value $needle is in the values of $haystack.
                 *
                 * @param mixed $needle the value to look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if $needle is present in the values of $haystack, false otherwise.
                 */
                'in' => function ($needle, $haystack) {
                    return in_array($needle, $haystack);
                },

                /**
                 * Check if the specified user (by user_id) is in a particular group.
                 *
                 * @param int $user_id the id of the user.
                 * @param int $group_id the id of the group.
                 * @return bool true if the user is in the group, false otherwise.
                 */
                'in_group' => function ($user_id, $group_id) {
                    $user = User::find($user_id);
                    return ($user->group_id == $group_id);
                },

                /**
                 * Check if the specified user (by user_id) is the master user.
                 *
                 * @param int $user_id the id of the user.
                 * @return bool true if the user id is equal to the id of the master account, false otherwise.
                 */
                'is_master' => function ($user_id) use ($config) {
                    // Need to use loose comparison for now, because some DBs return `id` as a string
                    return ($user_id == $config['reserved_user_ids.master']);
                },

                /**
                 * Check if all values in the array $needle are present in the values of $haystack.
                 *
                 * @param array[mixed] $needle the array whose values we should look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if every value in $needle is present in the values of $haystack, false otherwise.
                 */
                'subset' => function ($needle, $haystack) {
                    return count($needle) == count(array_intersect($needle, $haystack));
                },

                /**
                 * Check if all keys of the array $needle are present in the values of $haystack.
                 *
                 * This function is useful for whitelisting an array of key-value parameters.
                 * @param array[mixed] $needle the array whose keys we should look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if every key in $needle is present in the values of $haystack, false otherwise.
                 */
                'subset_keys' => function ($needle, $haystack) {
                    return count($needle) == count(array_intersect(array_keys($needle), $haystack));
                }
            ];

            $authorizer = new AuthorizationManager($c, $callbacks);
            return $authorizer;
        };

        /**
         * Loads the User object for the currently logged-in user.
         */
        $container['currentUser'] = function ($c) {
            $authenticator = $c->authenticator;

            return $authenticator->user();
        };

        $container['passwordHasher'] = function ($c) {
            $hasher = new Hasher();
            return $hasher;
        };

        /**
         * Returns a callback that forwards to dashboard if user is already logged in.
         */
        $container['redirect.onAlreadyLoggedIn'] = function ($c) {
            /**
             * This method is invoked when a user attempts to perform certain public actions when they are already logged in.
             *
             * @todo Forward to user's landing page or last visited page
             * @param \Psr\Http\Message\ServerRequestInterface $request
             * @param \Psr\Http\Message\ResponseInterface      $response
             * @param array $args
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                $redirect = $c->router->pathFor('dashboard');

                return $response->withRedirect($redirect, 302);
            };
        };

        /**
         * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
         */
        $container['redirect.onLogin'] = function ($c) {
            /**
             * This method is invoked when a user completes the login process.
             *
             * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
             * @param \Psr\Http\Message\ServerRequestInterface $request
             * @param \Psr\Http\Message\ResponseInterface      $response
             * @param array $args
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                // Backwards compatibility for the deprecated determineRedirectOnLogin service
                if ($c->has('determineRedirectOnLogin')) {
                    $determineRedirectOnLogin = $c->determineRedirectOnLogin;

                    return $determineRedirectOnLogin($response)->withStatus(200);
                }

                /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
                $authorizer = $c->authorizer;

                $currentUser = $c->authenticator->user();

                if ($authorizer->checkAccess($currentUser, 'uri_account_settings')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('settings'));
                } else {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('index'));
                }
            };
        };

        /**
         * Repository for password reset requests.
         */
        $container['repoPasswordReset'] = function ($c) {
            $classMapper = $c->classMapper;
            $config = $c->config;

            $repo = new PasswordResetRepository($classMapper, $config['password_reset.algorithm']);
            return $repo;
        };

        /**
         * Repository for verification requests.
         */
        $container['repoVerification'] = function ($c) {
            $classMapper = $c->classMapper;
            $config = $c->config;

            $repo = new VerificationRepository($classMapper, $config['verification.algorithm']);
            return $repo;
        };

        /**
         * Logger for logging the current user's activities to the database.
         *
         * Extend this service to push additional handlers onto the 'userActivity' log stack.
         */
        $container['userActivityLogger'] = function ($c) {
            $classMapper = $c->classMapper;
            $config = $c->config;
            $session = $c->session;

            $logger = new Logger('userActivity');

            $handler = new UserActivityDatabaseHandler($classMapper, 'activity');

            // Note that we get the user id from the session, not the currentUser service.
            // This is because the currentUser service may not reflect the actual user during login/logout requests.
            $currentUserIdKey = $config['session.keys.current_user_id'];
            $userId = isset($session[$currentUserIdKey]) ? $session[$currentUserIdKey] : $config['reserved_user_ids.guest'];
            $processor = new UserActivityProcessor($userId);

            $logger->pushProcessor($processor);
            $logger->pushHandler($handler);

            return $logger;
        };
    }
}
