<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Support\Exception\ForbiddenException;

/**
 * AdminController Class.
 *
 * Controller class for /dashboard URL.  Handles admin-related activities
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AdminController extends SimpleController
{
    /**
     * Renders the admin panel dashboard.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageDashboard(Request $request, Response $response, $args)
    {
        //** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_dashboard')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Probably a better way to do this
        $users = $classMapper->getClassMapping('user')::orderBy('created_at', 'desc')
                 ->take(8)
                 ->get();

        // Transform the `create_at` date in "x days ago" type of string
        $users->transform(function ($item, $key) {
            $item->registered = Carbon::parse($item->created_at)->diffForHumans();

            return $item;
        });

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->cache;

        // Get each sprinkle db version
        $sprinkles = $this->ci->sprinkleManager->getSprinkleNames();

        return $this->ci->view->render($response, 'pages/dashboard.html.twig', [
            'counter' => [
                'users'  => $classMapper->getClassMapping('user')::count(),
                'roles'  => $classMapper->getClassMapping('role')::count(),
                'groups' => $classMapper->getClassMapping('group')::count(),
            ],
            'info' => [
                'version' => [
                    'UF'       => \UserFrosting\VERSION,
                    'php'      => phpversion(),
                    'database' => EnvironmentInfo::database(),
                ],
                'database' => [
                    'name' => $config['db.default.database'],
                ],
                'environment' => $this->ci->environment,
                'path'        => [
                    'project' => \UserFrosting\ROOT_DIR,
                ],
            ],
            'sprinkles' => $sprinkles,
            'users'     => $users,
        ]);
    }

    /**
     * Clear the site cache.
     *
     * This route requires authentication.
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function clearCache(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'clear_cache')) {
            throw new ForbiddenException();
        }

        // Flush cache
        $this->ci->cache->flush();

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'CACHE.CLEARED');

        return $response->withJson([], 200);
    }

    /**
     * Renders the modal form to confirm cache deletion.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function getModalConfirmClearCache(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'clear_cache')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'modals/confirm-clear-cache.html.twig', [
            'form' => [
                'action' => 'api/dashboard/clear-cache',
            ],
        ]);
    }
}
