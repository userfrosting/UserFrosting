<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * Controller class for permission-related requests, including listing permissions, CRUD for permissions, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionController extends SimpleController
{
    /**
     * Returns info for a single permission.
     *
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     * @throws NotFoundException  If permission is not found
     */
    public function getInfo(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_permissions')) {
            throw new ForbiddenException();
        }

        $permissionId = $args['id'];

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $permission = $classMapper->getClassMapping('permission')::findInt($permissionId);

        // If the permission doesn't exist, return 404
        if (!$permission) {
            throw new NotFoundException();
        }

        // Get permission
        $result = $permission->load('users')->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns a list of Permissions.
     *
     * Generates a list of permissions, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function getList(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_permissions')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = $classMapper->createInstance('permission_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Returns a list of Users for a specified Permission.
     *
     * Generates a list of users, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function getUsers(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_permissions')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $params['permission_id'] = $args['id'];

        $sprunje = $classMapper->createInstance('permission_user_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders a page displaying a permission's information, in read-only mode.
     *
     * This checks that the currently logged-in user has permission to view permissions.
     * Note that permissions cannot be modified through the interface.  This is because
     * permissions are tighly coupled to the code and should only be modified by developers.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     * @throws NotFoundException  If permission is not found
     */
    public function pageInfo(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_permissions')) {
            throw new ForbiddenException();
        }

        $permissionId = $args['id'];

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $permission = $classMapper->getClassMapping('permission')::findInt($permissionId);

        // If the permission doesn't exist, return 404
        if (!$permission) {
            throw new NotFoundException();
        }

        return $this->ci->view->render($response, 'pages/permission.html.twig', [
            'permission' => $permission,
        ]);
    }

    /**
     * Renders the permission listing page.
     *
     * This page renders a table of permissions, with dropdown menus for admin actions for each permission.
     * Actions typically include: edit permission, delete permission.
     * This page requires authentication.
     *
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function pageList(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_permissions')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/permissions.html.twig');
    }
}
