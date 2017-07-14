<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Controller;

use Carbon\Carbon;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Database\Models\Version;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Support\Exception\ForbiddenException;

/**
 * AdminController Class
 *
 * Controller class for /dashboard URL.  Handles admin-related activities
 *
 * @author Alex Weissman (https://alexanderweissman.com)
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

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_dashboard')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Probably a better way to do this
        $users = $classMapper->staticMethod('user', 'orderBy', 'created_at', 'desc')
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
        $sprinkles = $this->ci->sprinkleManager->getSprinkleNames();

        return $this->ci->view->render($response, 'pages/dashboard.html.twig', [
            'counter' => [
                'users' => $classMapper->staticMethod('user', 'count'),
                'roles' => $classMapper->staticMethod('role', 'count'),
                'groups' => $classMapper->staticMethod('group', 'count')
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

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
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

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'clear_cache')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'modals/confirm-clear-cache.html.twig', [
            'form' => [
                'action' => 'api/dashboard/clear-cache',
            ]
        ]);
    }
}
