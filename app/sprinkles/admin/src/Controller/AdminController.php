<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Controller;

use Carbon\Carbon;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Account\Model\Group;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Model\Role;
use UserFrosting\Sprinkle\Core\Model\Version;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;

/**
 * AdminController Class
 *
 * Controller class for /admin URL.  Handles admin-related activities
 *
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class AdminController extends SimpleController
{

    /**
     * Renders the admin panel dashboard
     *
     */
    public function pageDashboard($request, $response, $args)
    {
        //** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_dashboard')) {
            throw new ForbiddenException();
        }

        // Probably a better way to do this
        $users = User::orderBy('created_at', 'desc')
               ->take(8)
               ->get();

        // Transform the `create_at` date in "x days ago" type of string
        $users->transform(function ($item, $key) {
            $item->registered = Carbon::parse($item->created_at)->diffForHumans();
            return $item;
        });

        /** @var Config $config */
        $config = $this->ci->config;

        /** @var Config $config */
        $cache = $this->ci->cache;

        // Get each sprinkle db version
        $sprinkles = $cache->rememberForever('uf_sprinklesVersion', function() {

            // The returned/cached data
            $sprinkles = array();

            // Get the sprinkles list
            $sprinklesList = $this->ci->sprinkleManager->getSprinkles();

            // Manually prepend the core sprinkle
            array_unshift($sprinklesList , 'core');

            // Get the data from the version table
            $versions = Version::all();

            // Load each sprinkle version
            foreach ($sprinklesList as $sprinkle) {

                // Get sprinkle db version number
                if ($sprinkleVersion = $versions->where('sprinkle', $sprinkle)->first()) {
                    $version = $sprinkleVersion->version;
                } else {
                    $version = null;
                }

                // Get the latest available migration in the file
                $migrations = array_reverse(glob(\UserFrosting\APP_DIR . \UserFrosting\DS .
                                                \UserFrosting\SPRINKLES_DIR_NAME . \UserFrosting\DS .
                                                $sprinkle . \UserFrosting\DS .
                                                'migrations' . \UserFrosting\DS .
                                                '*.php'));
                if (!empty($migrations)) {
                    $lastMigration = basename($migrations[0], '.php');
                    $migration = version_compare($version, $lastMigration, '<');
                } else {
                    $migration = false;
                }

                // Put the sprinkle data in the cached data
                $sprinkles[] = [
                    'name' => $sprinkle,
                    'version' => $version,
                    'migration' => $migration,
                ];
            }

            return $sprinkles;
        });

        return $this->ci->view->render($response, 'pages/dashboard.html.twig', [
            'counter' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'groups' => Group::count(),
            ],
            'info' => [
                'version' => [
                    'UF' => \UserFrosting\VERSION,
                    'php' => phpversion(),
                    'database' => EnvironmentInfo::database()
                ],
                'database' => [
                    'name' => $config['db.default.database']
                ],
                'environment' => $this->ci->environment,
                'path' => [
                    'project' => \UserFrosting\ROOT_DIR
                ]
            ],
            'sprinkles' => $sprinkles,
            'users' => $users
        ]);
    }

    /**
     * Clear the site cache.
     *
     * This route requires authentication.
     * Request type: POST
     */
    public function clearCache($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'clear_cache')) {
            throw new ForbiddenException();
        }

        // Flush cache
        $this->ci->cache->flush();

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'CACHE.CLEARED');

        return $response->withStatus(200);
    }

    /**
     * Renders the modal form to confirm cache deletion.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalConfirmClearCache($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'clear_cache')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'components/modals/confirm-clear-cache.html.twig', [
            'form' => [
                'action' => 'api/admin/clear-cache',
            ]
        ]);
    }
}
