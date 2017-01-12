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
use UserFrosting\Sprinkle\Account\Model\Role;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Admin\Sprunje\PermissionSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje;
use UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for role-related requests, including listing roles, CRUD for roles, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RoleController extends SimpleController
{
    /**
     * Processes the request to create a new role.
     *
     * Processes the request from the role creation form, checking that:
     * 1. The role name and slug are not already in use;
     * 2. The user has permission to create a new role;
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @see getModalCreateRole
     */
    public function createRole($request, $response, $args)
    {
        // Get POST parameters: name, slug, description
        $params = $request->getParsedBody();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_role')) {
            throw new ForbiddenException();
        }

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://role.json');

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
        if ($classMapper->staticMethod('role', 'where', 'name', $data['name'])->first()) {
            $ms->addMessageTranslated('danger', 'ROLE.NAME.IN_USE', $data);
            $error = true;
        }

        if ($classMapper->staticMethod('role', 'where', 'slug', $data['slug'])->first()) {
            $ms->addMessageTranslated('danger', 'ROLE.SLUG.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // All checks passed!  log events/activities and create role
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser) {
            // Create the role
            $role = $classMapper->createInstance('role', $data);

            // Store new role to database
            $role->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} created role {$role->name}.", [
                'type' => 'role_create',
                'user_id' => $currentUser->id
            ]);

            $ms->addMessageTranslated('success', 'ROLE.CREATION_SUCCESSFUL', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Processes the request to delete an existing role.
     *
     * Deletes the specified role.
     * Before doing so, checks that:
     * 1. The user has permission to delete this role;
     * 2. The role is not a default for new users;
     * 3. The role does not have any associated users;
     * 4. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: DELETE
     */
    public function deleteRole($request, $response, $args)
    {
        $role = $this->getRoleFromParams($args);

        // If the role doesn't exist, return 404
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_role', [
            'role' => $role
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Check that we are not deleting a default role
        $defaultRoles = explode(',', $config['site.registration.user_defaults.roles']);

        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (in_array($role->slug, $defaultRoles)) {
            $e = new BadRequestException();
            $e->addUserMessage('ROLE.DELETE_DEFAULT');
            throw $e;
        }

        // Check if there are any users associated with this role
        $countUsers = $role->users()->count();
        if ($countUsers > 0) {
            $e = new BadRequestException();
            $e->addUserMessage('ROLE.HAS_USERS');
            throw $e;
        }

        $roleName = $role->name;

        $role->delete();
        unset($role);

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'ROLE.DELETION_SUCCESSFUL', [
            'name' => $roleName
        ]);

        return $response->withStatus(200);
    }

    public function getModalConfirmDeleteRole($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $role = $this->getRoleFromParams($params);

        // If the role no longer exists, forward to main role listing page
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_role', [
            'role' => $role
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Check that we are not deleting a default role
        $defaultRoles = explode(',', $config['site.registration.user_defaults.roles']);

        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (in_array($role->slug, $defaultRoles)) {
            $e = new BadRequestException();
            $e->addUserMessage('ROLE.DELETE_DEFAULT');
            throw $e;
        }

        // Check if there are any users associated with this role
        $countUsers = $role->users()->count();
        if ($countUsers > 0) {
            $e = new BadRequestException();
            $e->addUserMessage('ROLE.HAS_USERS');
            throw $e;
        }

        return $this->ci->view->render($response, 'components/modals/confirm-delete-role.html.twig', [
            'role' => $role,
            'form' => [
                'action' => "api/roles/r/{$role->slug}",
            ]
        ]);
    }

    /**
     * Renders the modal form for creating a new role.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalCreateRole($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_role')) {
            throw new ForbiddenException();
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Create a dummy role to prepopulate fields
        $role = $classMapper->createInstance('role', []);

        $fieldNames = ['name', 'slug', 'description'];
        $fields = [
            'hidden' => [],
            'disabled' => []
        ];

        // Load validation rules
        $schema = new RequestSchema('schema://role.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/role.html.twig', [
            'role' => $role,
            'form' => [
                'action' => 'api/roles',
                'method' => 'POST',
                'fields' => $fields,
                'buttons' => [
                    'hidden' => [
                        'edit', 'delete'
                    ],
                    'submit_text' => 'Create role'
                ]
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing an existing role.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditRole($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $role = $this->getRoleFromParams($params);

        // If the role doesn't exist, return 404
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit basic fields "name", "slug", "description" for this role
        $fieldNames = ['name', 'slug', 'description'];
        if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
            'role' => $role,
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
        $schema = new RequestSchema('schema://role.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/role.html.twig', [
            'role' => $role,
            'form' => [
                'action' => "api/roles/r/{$role->slug}",
                'method' => 'PUT',
                'fields' => $fields,
                'buttons' => [
                    'hidden' => [
                        'edit', 'delete'
                    ],
                    'submit_text' => 'Update role'
                ]
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the modal form for editing a role's permissions.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditRolePermissions($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        $role = $this->getRoleFromParams($params);

        // If the role doesn't exist, return 404
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit "permissions" field for this role
        if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
            'role' => $role,
            'fields' => ['permissions']
        ])) {
            throw new ForbiddenException();
        }

        // Load validation rules
        $schema = new RequestSchema('schema://role.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/role-manage-permissions.html.twig', [
            'role' => $role,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Returns a list of Roles
     *
     * Generates a list of roles, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getRoles($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_roles')) {
            throw new ForbiddenException();
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new RoleSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Returns a list of Permissions for a specified Role.
     *
     * Generates a list of permissions, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getRolePermissions($request, $response, $args)
    {
        $role = $this->getRoleFromParams($args);

        // If the role no longer exists, forward to main role listing page
        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_role_permissions', [
            'role' => $role
        ])) {
            throw new ForbiddenException();
        }

        $this->ci->db;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new PermissionSprunje($classMapper, $params);
        $sprunje->extendQuery(function ($query) use ($role) {
            return $query->forRole($role->id);
        });

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders a page displaying a role's information, in read-only mode.
     *
     * This checks that the currently logged-in user has permission to view the requested role's info.
     * It checks each field individually, showing only those that you have permission to view.
     * This will also try to show buttons for deleting and editing the role.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageRole($request, $response, $args)
    {
        $role = $this->getRoleFromParams($args);

        // If the role no longer exists, forward to main role listing page
        if (!$role) {
            $redirectPage = $this->ci->router->pathFor('uri_roles');
            return $response->withRedirect($redirectPage, 404);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_role', [
                'role' => $role
            ])) {
            throw new ForbiddenException();
        }

        // Determine fields that currentUser is authorized to view
        $fieldNames = ['name', 'slug', 'description'];

        // Generate form
        $fields = [
            'hidden' => [],
            'disabled' => []
        ];

        foreach ($fieldNames as $field) {
            if ($authorizer->checkAccess($currentUser, 'view_role_field', [
                'role' => $role,
                'property' => $field
            ])) {
                $fields['disabled'][] = $field;
            } else {
                $fields['hidden'][] = $field;
            }
        }

        return $this->ci->view->render($response, 'pages/role.html.twig', [
            'role' => $role,
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
     * Renders the role listing page.
     *
     * This page renders a table of roles, with dropdown menus for admin actions for each role.
     * Actions typically include: edit role, delete role.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageRoles($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_roles')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/roles.html.twig');
    }

    /**
     * Processes the request to update an existing role's details.
     *
     * Processes the request from the role update form, checking that:
     * 1. The role name/slug are not already in use;
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: PUT
     * @see getModalRoleEdit
     */
    public function updateRole($request, $response, $args)
    {
        // Get the role based on slug in the URL
        $role = $this->getRoleFromParams($args);

        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Get PUT parameters: (name, slug, description)
        $params = $request->getParsedBody();

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://role.json');

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

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this role
        if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
            'role' => $role,
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
            $data['name'] != $role->name &&
            $classMapper->staticMethod('role', 'where', 'name', $data['name'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'ROLE.NAME.IN_USE', $data);
            $error = true;
        }

        if (
            isset($data['slug']) &&
            $data['slug'] != $role->slug &&
            $classMapper->staticMethod('role', 'where', 'slug', $data['slug'])->first()
        ) {
            $ms->addMessageTranslated('danger', 'ROLE.SLUG.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        // Update the role and generate success messages
        foreach ($data as $name => $value) {
            if ($value != $role->$name){
                $role->$name = $value;
            }
        }

        $role->save();

        $ms->addMessageTranslated('success', 'ROLE.UPDATE', [
            'name' => $role->name
        ]);

        return $response->withStatus(200);
    }

    /**
     * Processes the request to update a specific field for an existing role, including permissions.
     *
     * Processes the request from the role update form, checking that:
     * 1. The logged-in user has the necessary permissions to update the putted field(s);
     * 2. The submitted data is valid.
     * This route requires authentication.
     * Request type: PUT
     */
    public function updateRoleField($request, $response, $args)
    {
        // Get the username from the URL
        $role = $this->getRoleFromParams($args);

        if (!$role) {
            throw new NotFoundException($request, $response);
        }

        // Get key->value pair from URL and request body
        $fieldName = $args['field'];

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled resource - check that currentUser has permission to edit the specified field for this user
        if (!$authorizer->checkAccess($currentUser, 'update_role_field', [
            'role' => $role,
            'fields' => [$fieldName]
        ])) {
            throw new ForbiddenException();
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // Get PUT parameters: value
        $put = $request->getParsedBody();

        if (!isset($put['value'])) {
            throw new BadRequestException();
        }

        $params = [
            $fieldName => $put['value']
        ];

        // Validate key -> value pair

        // Load the request schema
        $schema = new RequestSchema('schema://role-fields.json');

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

        // Get validated and transformed value
        $fieldValue = $data[$fieldName];

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        if ($fieldName == "permissions") {
            $newPermissions = collect($fieldValue)->pluck('permission_id')->all();
            $role->permissions()->sync($newPermissions);
        } else {
            $role->$fieldName = $fieldValue;
            $role->save();
        }

        $ms->addMessageTranslated('success', 'DETAILS_UPDATED', [
            'name' => $role->name
        ]);

        return $response->withStatus(200);
    }

    protected function getRoleFromParams($params)
    {
        // Load the request schema
        $schema = new RequestSchema('schema://get-role.json');

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

        // Get the role
        $role = $classMapper->staticMethod('role', 'where', 'slug', $data['slug'])
            ->first();

        return $role;
    }
}
