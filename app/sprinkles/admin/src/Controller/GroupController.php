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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Account\Model\Group;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Admin\Sprunje\GroupSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for group-related requests, including listing groups, CRUD for groups, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class GroupController extends SimpleController
{
    /**
     * Processes the request to create a new group.
     *
     * Processes the request from the group creation form, checking that:
     * 1. The group name and slug are not already in use;
     * 2. The user has permission to create a new group;
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @see getModalCreateGroup
     */
    public function createGroup($request, $response, $args)
    {
        // Get POST parameters: name, slug, icon, description
        $params = $request->getParsedBody();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_group')) {
            throw new ForbiddenException();
        }

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://group.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        $this->ci->db;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if name or slug already exists
        if ($classMapper->staticMethod('group', 'where', 'name', $data['name'])->first()) {
            $ms->addMessageTranslated('danger', 'GROUP.NAME.IN_USE', $data);
            $error = true;
        }

        if ($classMapper->staticMethod('group', 'where', 'slug', $data['slug'])->first()) {
            $ms->addMessageTranslated('danger', 'GROUP.SLUG.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // All checks passed!  log events/activities and create group
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser) {
            // Create the group
            $group = $classMapper->createInstance('group', $data);

            // Store new group to database
            $group->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} created group {$group->name}.", [
                'type' => 'group_create',
                'user_id' => $currentUser->id
            ]);

            $ms->addMessageTranslated('success', 'GROUP.CREATION_SUCCESSFUL', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing group.
     *
     * Deletes the specified group, removing associations with any users and any group-specific authorization rules.
     * Before doing so, checks that:
     * 1. The group is deleteable (as specified in the `can_delete` column in the database);
     * 2. The group is not currently set as the default primary group;
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @param int $group_id the id of the group to delete.
     */
    public function deleteGroup($request, $response, $args)
    {
        $group = $this->getGroupFromParams($args);

        // If the group doesn't exist, return 404
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_group', [
            'group' => $group
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Check that we are not deleting the default group
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($group->slug == $config['site.registration.user_defaults.group']) {
            $e = new BadRequestException();
            $e->addUserMessage('GROUP.DELETE_DEFAULT');
            throw $e;
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if there are any users in this group
        $countGroupUsers = $classMapper->staticMethod('user', 'where', 'group_id', $group->id)->count();
        if ($countGroupUsers > 0) {
            $e = new BadRequestException();
            $e->addUserMessage('GROUP.NOT_EMPTY');
            throw $e;
        }

        $groupName = $group->name;

        $group->delete();
        unset($group);

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'GROUP.DELETION_SUCCESSFUL', [
            'name' => $groupName
        ]);

        return $response->withStatus(200);
    }

    /**
     * Returns a list of Groups
     *
     * Generates a list of groups, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getGroups($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_groups')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $this->ci->db;

        $sprunje = new GroupSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    public function getGroupUsers($request, $response, $args)
    {
        $group = $this->getGroupFromParams($args);

        // If the group no longer exists, forward to main group listing page
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_group_users', [
            'group' => $group
        ])) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $this->ci->db;

        $sprunje = new UserSprunje($classMapper, $params);
        $sprunje->extendQuery(function ($query) use ($group) {
            return $query->where('group_id', $group->id);
        });

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    public function getModalConfirmDeleteGroup($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $group = $this->getGroupFromParams($params);

        // If the group no longer exists, forward to main group listing page
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_group', [
            'group' => $group
        ])) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if there are any users in this group
        $countGroupUsers = $classMapper->staticMethod('user', 'where', 'group_id', $group->id)->count();
        if ($countGroupUsers > 0) {
            $e = new BadRequestException();
            $e->addUserMessage('GROUP.NOT_EMPTY');
            throw $e;
        }

        return $this->ci->view->render($response, 'components/modals/confirm-delete-group.html.twig', [
            'group' => $group,
            'form' => [
                'action' => "api/groups/g/{$group->slug}",
            ]
        ]);
    }

    /**
     * Renders the modal form for creating a new group.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalCreateGroup($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_group')) {
            throw new ForbiddenException();
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Create a dummy group to prepopulate fields
        $group = $classMapper->createInstance('group', []);

        $group->icon = 'fa fa-user';

        $fieldNames = ['name', 'slug', 'icon', 'description'];
        $fields = [
            'hidden' => [],
            'disabled' => []
        ];

        // Load validation rules
        $schema = new RequestSchema('schema://group.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/group.html.twig', [
            'group' => $group,
            'form' => [
                'action' => 'api/groups',
                'method' => 'POST',
                'fields' => $fields,
                'buttons' => [
                    'hidden' => [
                        'edit', 'delete'
                    ],
                    'submit_text' => 'Create group'
                ]
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing an existing group.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditGroup($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $group = $this->getGroupFromParams($params);

        // If the group doesn't exist, return 404
        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit basic fields "name", "slug", "icon", "description" for this group
        $fieldNames = ['name', 'slug', 'icon', 'description'];
        if (!$authorizer->checkAccess($currentUser, 'update_group_field', [
            'group' => $group,
            'fields' => $fieldNames
        ])) {
            throw new ForbiddenException();
        }

        // Generate form
        $fields = [
            'hidden' => [],
            'disabled' => []
        ];

        // Load validation rules
        $schema = new RequestSchema('schema://group.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/group.html.twig', [
            'group' => $group,
            'form' => [
                'action' => "api/groups/g/{$group->slug}",
                'method' => 'PUT',
                'fields' => $fields,
                'buttons' => [
                    'hidden' => [
                        'edit', 'delete'
                    ],
                    'submit_text' => 'Update group'
                ]
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders a page displaying a group's information, in read-only mode.
     *
     * This checks that the currently logged-in user has permission to view the requested group's info.
     * It checks each field individually, showing only those that you have permission to view.
     * This will also try to show buttons for deleting, and editing the group.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageGroup($request, $response, $args)
    {
        $group = $this->getGroupFromParams($args);

        $groupsPage = '';

        // If the group no longer exists, forward to main group listing page
        if (!$group) {
            $usersPage = $this->ci->router->pathFor('uri_groups');
            return $response->withRedirect($groupsPage, 404);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_group', [
                'group' => $group
            ])) {
            throw new ForbiddenException();
        }

        // Determine fields that currentUser is authorized to view
        $fieldNames = ['name', 'slug', 'icon', 'description'];

        // Generate form
        $fields = [
            'hidden' => [],
            'disabled' => []
        ];

        foreach ($fieldNames as $field) {
            if ($authorizer->checkAccess($currentUser, 'view_group_field', [
                'group' => $group,
                'property' => $field
            ])) {
                $fields['disabled'][] = $field;
            } else {
                $fields['hidden'][] = $field;
            }
        }

        return $this->ci->view->render($response, 'pages/group.html.twig', [
            'group' => $group,
            'form' => [
                'fields' => $fields,
                'buttons' => [
                    'hidden' => [
                        'submit', 'cancel'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Renders the group listing page.
     *
     * This page renders a table of groups, with dropdown menus for admin actions for each group.
     * Actions typically include: edit group, delete group.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageGroups($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_groups')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/groups.html.twig');
    }

    /**
     * Processes the request to update an existing group's details.
     *
     * Processes the request from the group update form, checking that:
     * 1. The group name/slug are not already in use;
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: PUT
     * @see getModalGroupEdit
     */
    public function updateGroup($request, $response, $args)
    {
        // Get the username from the URL
        $group = $this->getGroupFromParams($args);

        if (!$group) {
            throw new NotFoundException($request, $response);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://group.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        // Determine targeted fields
        $fieldNames = [];
        foreach ($data as $name => $value) {
            $fieldNames[] = $name;
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this group
        if (!$authorizer->checkAccess($currentUser, 'update_group_field', [
            'group' => $group,
            'fields' => array_values(array_unique($fieldNames))
        ])) {
            throw new ForbiddenException();
        }

        $this->ci->db;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Check if name or slug already exists
        if (
            isset($data['name']) &&
            $data['name'] != $group->name &&
            $classMapper->staticMethod('group', 'where', 'name', $data['name'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'GROUP.NAME.IN_USE', $data);
            $error = true;
        }

        if (
            isset($data['slug']) &&
            $data['slug'] != $group->slug &&
            $classMapper->staticMethod('group', 'where', 'slug', $data['slug'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'GROUP.SLUG.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Update the group and generate success messages
        foreach ($data as $name => $value) {
            if ($value != $group->$name){
                $group->$name = $value;
            }
        }

        $group->save();

        $ms->addMessageTranslated('success', 'GROUP_UPDATE', [
            'name' => $group->name
        ]);

        return $response->withStatus(200);
    }

    protected function getGroupFromParams($params)
    {
        // Load the request schema
        $schema = new RequestSchema('schema://get-group.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and throw exception on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException();
            foreach ($validator->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        $this->ci->db;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get the group
        $group = $classMapper->staticMethod('group', 'where', 'slug', $data['slug'])
            ->first();

        return $group;
    }
}
