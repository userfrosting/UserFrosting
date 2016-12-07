<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Controller;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Account\Model\Group;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Util\Password;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\BadRequest;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for user-related requests, including listing users, CRUD for users, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/navigating/#structure
 */
class UserController extends SimpleController
{
    /**
     * Renders a page displaying a user's information, in read-only mode.
     *
     * This checks that the currently logged-in user has permission to view the requested user's info.
     * It checks each field individually, showing only those that you have permission to view.
     * This will also try to show buttons for activating, disabling/enabling, deleting, and editing the user.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageUser($request, $response, $args)
    {
        // Get the user to view
        $user_name = $args['user_name'];

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;
        $user = $classMapper->staticMethod('user', 'where', 'user_name', $user_name)->first();

        // If the user no longer exists, forward to main user listing page
        if (!$user) {
            $usersPage = $this->ci->router->pathFor('uri_users');
            return $response->withRedirect($usersPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_user', [
                'user' => $user
            ])) {
            throw new ForbiddenException();
        }

        // Get list of all available locales.  Wait, why?
        $locales = $this->ci->translator->getAvailableLocales();

        $themes = [];
        
        // Determine fields that currentUser is authorized to view
        $fields = ['name', 'email', 'locale', 'theme'];
        $show_fields = [];
        $disabled_fields = [];
        $hidden_fields = ['user_name', 'group'];

        foreach ($fields as $field) {
            if ($authorizer->checkAccess($currentUser, "view_user_field", [
                "user" => $user,
                "property" => $field
            ])) {
                $disabled_fields[] = $field;
            } else {
                $hidden_fields[] = $field;
            }
        }

        // Always disallow editing username
        $disabled_fields[] = "user_name";

        return $this->ci->view->render($response, 'pages/user.html.twig', [
            "user" => $user,
            "locales" => $locales,
            "form" => [
                "fields" => [
                    "disabled" => $disabled_fields,
                    "hidden" => $hidden_fields
                ],
                "buttons" => [
                    "hidden" => [
                        "submit", "cancel"
                    ]
                ]
            ]
            /*
            "groups" => $group_list,
            */
        ]);
    }

    /**
     * Renders the user listing page.
     *
     * This page renders a table of users, with dropdown menus for admin actions for each user.
     * Actions typically include: edit user details, activate user, enable/disable user, delete user.
     * This page requires authentication.
     * Request type: GET
     * @param string $primary_group_name optional.  If specified, will only display users in that particular primary group.
     * @param bool $paginate_server_side optional.  Set to true if you want UF to load each page of results via AJAX on demand, rather than all at once.
     * @todo implement interface to modify user-assigned authorization hooks and permissions
     */
    public function pageUsers($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, "pages/users.html.twig");
    }


    /**
     * Returns a list of Users
     *
     * Generates a list of users, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getUsers($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();
        
        $filters = isset($params['filters']) ? $params['filters'] : [];
        $size = isset($params['size']) ? $params['size'] : null;
        $page = isset($params['page']) ? $params['page'] : null;
        $sortField = isset($params['sort_field']) ? $params['sort_field'] : "user_name";
        $sortOrder = isset($params['sort_order']) ? $params['sort_order'] : "asc";

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $this->ci->db;

        $query = $classMapper->createInstance('user');

        // Allow filtering by last activity
        if (isset($filters['last_activity'])) {
            $activityFilter = $filters['last_activity'];
        } else {
            $activityFilter = null;
        }

        // Custom sort fields
        if ($sortField == "last_activity") {
            $sortField = "last_activity_at";
        } else if ($sortField == "name") {
            $sortField = "last_name";
        } 

        // Join user's most recent activity
        $query = $query->joinLastActivity($activityFilter)->with('lastActivity');

        // Count unpaginated total
        $total = $query->count();
        
        // Apply filters
        $filtersApplied = false;
        foreach ($filters as $name => $value) {
            if ($name == 'last_activity') {
                continue;
            }
            
            if ($name == 'name') {
                $query = $query->like('first_name', $value)
                                ->orLike('last_name', $value)
                                ->orLike('email', $value);
            } else {
                $query = $query->like($name, $value);
            }
            
            $filtersApplied = true;
        }
        
        $totalFiltered = $query->count();
        
        $query = $query->orderBy($sortField, $sortOrder);    
        
        // Paginate
        if (($page !== null) && ($size !== null)) {
            $offset = $size*$page;
            $query = $query->skip($offset)->take($size);
        }

        $collection = collect($query->get());

        // Exclude password field from results
        $collection->transform(function ($item, $key) {
            unset($item['password']);
            return $item;
        });

        $result = [
            "count" => $total,
            "rows" => $collection->values()->toArray(),
            "count_filtered" => $totalFiltered
        ];
    
        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    public function pageGroupUsers($request, $response, $args)
    {
            // Optional filtering by primary group
        if ($primary_group_name){
            $primary_group = Group::where('name', $primary_group_name)->first();

            if (!$primary_group)
                $this->_app->notFound();

            // Access-controlled page
            if (!$this->_app->user->checkAccess('uri_group_users', ['primary_group_id' => $primary_group->id])){
                $this->_app->notFound();
            }

            if (!$paginate_server_side) {
                $user_collection = User::where('primary_group_id', $primary_group->id)->get();
                $user_collection->getRecentEvents('sign_in');
                $user_collection->getRecentEvents('sign_up', 'sign_up_time');
            }
            $name = $primary_group->name;
            $icon = $primary_group->icon;
        }
    }

    /**
     * Renders the modal form for creating a new user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * The form can be rendered in "modal" (for popup) or "panel" mode, depending on the value of the GET parameter `render`.
     * If the currently logged-in user has permission to modify user group membership, then the group toggles will be displayed.
     * Otherwise, the user will be added to the default groups automatically.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalCreateUser($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_user')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get a list of all groups
        $groups = $classMapper->staticMethod('group', 'all');
        
        /*
        // Get a list of all locales
        $locale_list = $this->_app->site->getLocales();

        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primary_group = Group::where("is_default", GROUP_DEFAULT_PRIMARY)->first();

        // If there is no default primary group, there is a problem.  Show an error message for now.
        if (!$primary_group){
            $this->_app->alerts->addMessageTranslated("danger", "GROUP_DEFAULT_PRIMARY_NOT_DEFINED");
            $this->_app->halt(500);
        }

        // Get the default groups as a dictionary
        $default_groups = Group::all()->where("is_default", GROUP_DEFAULT)->getDictionary();

        // Set default groups, including default primary group
        foreach ($groups as $group_id => $group){
            $group_list[$group_id] = $group->export();
            if (isset($default_groups[$group_id]) || $group_id == $primary_group->id)
                $group_list[$group_id]['member'] = true;
            else
                $group_list[$group_id]['member'] = false;
        }

        $data['primary_group_id'] = $primary_group->id;
        // Set default title for new users
        $data['title'] = $primary_group->new_user_title;
        // Set default locale
        $data['locale'] = $this->_app->site->default_locale;
        */
        
        // Create a dummy user to prepopulate fields
        $user = $classMapper->createInstance('user', []);

        // Load validation rules
        $schema = new RequestSchema('schema://create-user.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/user.html.twig', [
            "user" => $user,
            "groups" => $groups,
            "modal" => [
                
            ],
            "form" => [
                "action" => "api/users",
                "fields" => [
                    "disabled" => $disabled_fields,
                    "hidden" => $hidden_fields
                ],
                "buttons" => [
                    "hidden" => [
                        "edit", "enable", "delete", "activate"
                    ],
                    "submit_text" => "Create user"
                ]
            ],
            "page" => [
                "validators" => $validator->rules('json', false)
            ]
        ]);
        
        /*
        $this->_app->render($template, [
            "box_id" => $get['box_id'],
            "box_title" => "Create User",
            
        ]);
        */
    }

    /**
     * Renders the form for editing an existing user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * The form can be rendered in "modal" (for popup) or "panel" mode, depending on the value of the GET parameter `render`.
     * For each field, we will first check if the currently logged-in user has permission to update the field.  If so,
     * the field will be rendered as editable.  If not, we will check if they have permission to view the field.  If so,
     * it will be displayed but disabled.  If they have neither permission, the field will be hidden.
     * This page requires authentication.
     * Request type: GET
     * @param int $user_id the id of the user to edit.
     */
    public function formUserEdit($user_id){
        // Get the user to edit
        $target_user = User::find($user_id);

        // Access-controlled resource
        if (!$this->_app->user->checkAccess('uri_users') && !$this->_app->user->checkAccess('uri_group_users', ['primary_group_id' => $target_user->primary_group_id])){
            $this->_app->notFound();
        }

        $get = $this->_app->request->get();

        if (isset($get['render']))
            $render = $get['render'];
        else
            $render = "modal";

        // Get a list of all groups
        $groups = Group::get();

        // Get a list of all locales
        $locale_list = $this->_app->site->getLocales();

        // Determine which groups this user is a member of
        $user_groups = $target_user->getGroups();
        foreach ($groups as $group){
            $group_id = $group->id;
            $group_list[$group_id] = $group->export();
            if (isset($user_groups[$group_id]))
                $group_list[$group_id]['member'] = true;
            else
                $group_list[$group_id]['member'] = false;
        }

        if ($render == "modal")
            $template = "components/common/user-info-modal.twig";
        else
            $template = "components/common/user-info-panel.twig";

        // Determine authorized fields
        $fields = ['display_name', 'email', 'title', 'locale', 'groups', 'primary_group_id'];
        $show_fields = [];
        $disabled_fields = [];
        $hidden_fields = [];
        foreach ($fields as $field){
            if ($this->_app->user->checkAccess("update_account_setting", ["user" => $target_user, "property" => $field]))
                $show_fields[] = $field;
            else if ($this->_app->user->checkAccess("view_account_setting", ["user" => $target_user, "property" => $field]))
                $disabled_fields[] = $field;
            else
                $hidden_fields[] = $field;
        }

        // Always disallow editing username
        $disabled_fields[] = "user_name";

        // Load validator rules
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/user-update.json");
        $this->_app->jsValidator->setSchema($schema);

        $this->_app->render($template, [
            "box_id" => $get['box_id'],
            "box_title" => "Edit User",
            "submit_button" => "Update user",
            "form_action" => $this->_app->site->uri['public'] . "/users/u/$user_id",
            "target_user" => $target_user,
            "groups" => $group_list,
            "locales" => $locale_list,
            "fields" => [
                "disabled" => $disabled_fields,
                "hidden" => $hidden_fields
            ],
            "buttons" => [
                "hidden" => [
                    "edit", "enable", "delete", "activate"
                ]
            ],
            "validators" => $this->_app->jsValidator->rules()
        ]);
    }

    /**
     * Renders the modal form for editing a user's password.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditUserPassword($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;
        $user = $classMapper->staticMethod('user', 'where', 'id', $params['user_id'])->first();

        // If the user no longer exists, forward to main user listing page
        if (!$user) {
            $usersPage = $this->ci->router->pathFor('uri_users');
            return $response->withStatus(400);
        }

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'update_user_field', ['user' => $user, 'property' => 'password'])) {
            throw new ForbiddenException();
        }

        // Load validation rules
        $schema = new RequestSchema('schema://user-password.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'components/modals/user-set-password.html.twig', [
            'user' => $user,
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    public function getModalConfirmDeleteUser($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;
        $user = $classMapper->staticMethod('user', 'where', 'id', $params['user_id'])->first();

        // If the user no longer exists, forward to main user listing page
        if (!$user) {
            $usersPage = $this->ci->router->pathFor('uri_users');
            return $response->withStatus(400);
        }

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_user', ['user' => $user])) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'components/modals/confirm-delete-user.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Processes the request to create a new user (from the admin controls).
     *
     * Processes the request from the user creation form, checking that:
     * 1. The username and email are not already in use;
     * 2. The logged-in user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: POST
     * @see formUserCreate
     */
    public function createUser(){
        $post = $this->_app->request->post();

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/user-create.json");

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Access-controlled resource
        if (!$this->_app->user->checkAccess('create_account')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        // Sanitize data
        $rf->sanitize();

        // Validate, and halt on validation errors.
        $error = !$rf->validate(true);

        // Get the filtered data
        $data = $rf->data();

        // Remove csrf_token from object data
        $rf->removeFields(['csrf_token']);

        // Perform desired data transformations on required fields.  Is this a feature we could add to Fortress?
        $data['display_name'] = trim($data['display_name']);
        $data['flag_verified'] = 1;
        // Set password as empty on initial creation.  We will then send email so new user can set it themselves via secret token
        $data['password'] = "";

        // Check if username or email already exists
        if (User::where('user_name', $data['user_name'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_USERNAME_IN_USE", $data);
            $error = true;
        }

        if (User::where('email', $data['email'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
            $error = true;
        }

        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }

        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();

        // Set default values if not specified or not authorized
        if (!isset($data['locale']) || !$this->_app->user->checkAccess("update_account_setting", ["property" => "locale"]))
            $data['locale'] = $this->_app->site->default_locale;

        if (!isset($data['title']) || !$this->_app->user->checkAccess("update_account_setting", ["property" => "title"])) {
            // Set default title for new users
            $data['title'] = $primaryGroup->new_user_title;
        }

        if (!isset($data['primary_group_id']) || !$this->_app->user->checkAccess("update_account_setting", ["property" => "primary_group_id"])) {
            $data['primary_group_id'] = $primaryGroup->id;
        }

        // Set groups to default groups if not specified or not authorized to set groups
        if (!isset($data['groups']) || !$this->_app->user->checkAccess("update_account_setting", ["property" => "groups"])) {
            $default_groups = Group::where('is_default', GROUP_DEFAULT)->get();
            $data['groups'] = [];
            foreach ($default_groups as $group){
                $group_id = $group->id;
                $data['groups'][$group_id] = "1";
            }
        }

        // Create the user
        $user = new User($data);

        // Add user to groups, including selected primary group
        $user->addGroup($data['primary_group_id']);
        foreach ($data['groups'] as $group_id => $is_member) {
            if ($is_member == "1"){
                $user->addGroup($group_id);
            }
        }

        // Create events - account creation and password reset
        $user->newEventSignUp($this->_app->user);
        $user->newEventPasswordReset();

        // Save user again after creating events
        $user->save();

        // Send an email to the user's email address to set up password
        $twig = $this->_app->view()->getEnvironment();
        $template = $twig->loadTemplate("mail/password-create.twig");
        $notification = new Notification($template);
        $notification->fromWebsite();      // Automatically sets sender and reply-to
        $notification->addEmailRecipient($user->email, $user->display_name, [
            'user' => $user,
            'create_password_expiration' => $this->_app->site->create_password_expiration / 3600 . " hours"
        ]);

        // Success message even if we can't email them
        $ms->addMessageTranslated("success", "ACCOUNT_CREATION_COMPLETE", $data);

        try {
            $notification->send();
        } catch (\phpmailerException $e){
            $ms->addMessageTranslated("danger", "MAIL_ERROR");
            error_log('Mailer Error: ' . $e->errorMessage());
            $this->_app->halt(500);
        }

    }

    /**
     * Processes the request to update an existing user's details, including enabled/disabled status and activation status.
     *
     * Processes the request from the user update form, checking that:
     * 1. The target user's new email address, if specified, is not already in use;
     * 2. The logged-in user has the necessary permissions to update the posted field(s);
     * 3. We're not trying to disable the master account;
     * 4. The submitted data is valid.
     * This route requires authentication.
     * Request type: POST
     * @param int $user_id the id of the user to edit.
     * @see formUserEdit
     */
    public function updateUser($user_id){
        $post = $this->_app->request->post();

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/user-update.json");

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Get the target user
        $target_user = User::find($user_id);

        // Get the target user's groups
        $groups = $target_user->getGroups();

        /*
        // Access control for entire page
        if (!$this->_app->user->checkAccess('uri_update_user')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }
        */

        // Only the master account can edit the master account!
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (($target_user->id == $this->_app->config('user_id_master')) && $this->_app->user->id != $this->_app->config('user_id_master')) {
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }

        // Remove csrf_token
        unset($post['csrf_token']);

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        if (isset($post['passwordc'])){
            unset($post['passwordc']);
        }

        // Check authorization for submitted fields, if the value has been changed
        foreach ($post as $name => $value) {
            if ($name == "groups" || ($target_user->attributeExists($name) && $post[$name] != $target_user->$name)){
                // Check authorization
                if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $target_user, 'property' => $name])){
                    $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                    $this->_app->halt(403);
                }
            } else if (!$target_user->attributeExists($name)) {
                $ms->addMessageTranslated("danger", "NO_DATA");
                $this->_app->halt(400);
            }
        }

        // Check that we are not disabling the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (($target_user->id == $this->_app->config('user_id_master')) && isset($post['flag_enabled']) && $post['flag_enabled'] == "0"){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLE_MASTER");
            $this->_app->halt(403);
        }

        // Check that the email address is not in use
        if (isset($post['email']) && $post['email'] != $target_user->email && User::where('email', $post['email'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $post);
            $this->_app->halt(400);
        }

        // Sanitize
        $rf->sanitize();

        // Validate, and halt on validation errors.
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }

        // Remove passwordc
        $rf->removeFields(['passwordc']);

        // Get the filtered data
        $data = $rf->data();

        // Update user groups
        if (isset($data['groups'])){
            foreach ($data['groups'] as $group_id => $is_member) {
                if ($is_member == "1" && !isset($groups[$group_id])){
                    $target_user->addGroup($group_id);
                } else if ($is_member == "0" && isset($groups[$group_id])){
                    $target_user->removeGroup($group_id);
                }
            }
            unset($data['groups']);
        }

        // Hash password
        if (isset($data['password'])){
            $data['password'] = Authentication::hashPassword($data['password']);
        }

        // Update the user and generate success messages
        foreach ($data as $name => $value){
            if ($value != $target_user->$name){
                $target_user->$name = $value;
                // Custom success messages (optional)
                if ($name == "flag_enabled") {
                    if ($value == "1")
                        $ms->addMessageTranslated("success", "ACCOUNT_ENABLE_SUCCESSFUL", ["user_name" => $target_user->user_name]);
                    else
                        $ms->addMessageTranslated("success", "ACCOUNT_DISABLE_SUCCESSFUL", ["user_name" => $target_user->user_name]);
                }
                if ($name == "flag_verified") {
                    $ms->addMessageTranslated("success", "ACCOUNT_MANUALLY_ACTIVATED", ["user_name" => $target_user->user_name]);
                }
            }
        }

        // If we're generating a password reset, create the corresponding event and shoot off an email
        if (isset($data['flag_password_reset']) && ($data['flag_password_reset'] == "1")){
            // Recheck auth
            if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $target_user, 'property' => 'flag_password_reset'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $this->_app->halt(403);
            }
            // New password reset event - bypass any rate limiting
            $target_user->newEventPasswordReset();
            $target_user->save();
            // Email the user asking to confirm this change password request
            $twig = $this->_app->view()->getEnvironment();
            $template = $twig->loadTemplate("mail/password-reset.twig");
            $notification = new Notification($template);
            $notification->fromWebsite();      // Automatically sets sender and reply-to
            $notification->addEmailRecipient($target_user->email, $target_user->display_name, [
                "user" => $target_user,
                "request_date" => date("Y-m-d H:i:s")
            ]);

            try {
                $notification->send();
            } catch (\phpmailerException $e){
                $ms->addMessageTranslated("danger", "MAIL_ERROR");
                error_log('Mailer Error: ' . $e->errorMessage());
                $this->_app->halt(500);
            }

            $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SENT", ["user_name" => $target_user->user_name]);
        }

        $ms->addMessageTranslated("success", "ACCOUNT_DETAILS_UPDATED", ["user_name" => $target_user->user_name]);
        $target_user->save();
    }

    /**
     * Processes the request to delete an existing user.
     *
     * Deletes the specified user, removing associations with any groups and any user-specific authorization rules.
     * Before doing so, checks that:
     * 1. You are not trying to delete the master account;
     * 2. You have permission to delete user user accounts.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @param int $user_id the id of the user to delete.
     */
    public function deleteUser($user_id){
        $post = $this->_app->request->post();

        // Get the target user
        $target_user = User::find($user_id);

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Check authorization
        if (!$this->_app->user->checkAccess('delete_account', ['user' => $target_user])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }

        // Check that we are not disabling the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (($target_user->id == $this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "ACCOUNT_DELETE_MASTER");
            $this->_app->halt(403);
        }

        $ms->addMessageTranslated("success", "ACCOUNT_DELETION_SUCCESSFUL", ["user_name" => $target_user->user_name]);
        $target_user->delete();
        unset($target_user);
    }
}
