<?php

    /**
     * Routes for /groups/* URLs.  Handles group-related activities, including listing groups, CRUD for groups, etc.
     *
     * @package UserFrosting
     * @author Alex Weissman
     */
    
    use UserFrosting as UF;
    
    global $app;       
    
    /**
     * Renders the form for creating a new group.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * The form can be rendered in "modal" (for popup) or "panel" mode, depending on the value of the GET parameter `render`
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     */
    $app->get('/forms/groups/?', function () use ($app) {
        // Access-controlled resource
        if (!$app->user->checkAccess('create_group')){
            $app->notFound();
        }

        $get = $app->request->get();

        if (isset($get['render']))
            $render = $get['render'];
        else
            $render = "modal";

        // Get a list of all themes
        $theme_list = $app->site->getThemes();

        // Set default values
        $data['is_default'] = "0";
        // Set default title for new users
        $data['new_user_title'] = "New User";
        // Set default theme
        $data['theme'] = "default";
        // Set default icon
        $data['icon'] = "fa fa-user";
        // Set default landing page
        $data['landing_page'] = "dashboard";

        // Create a dummy Group to prepopulate fields
        $group = new UF\Group($data);

        if ($render == "modal")
            $template = "components/common/group-info-modal.twig";
        else
            $template = "components/common/group-info-panel.twig";

        // Determine authorized fields
        $fields = ['name', 'new_user_title', 'landing_page', 'theme', 'is_default', 'icon'];
        $show_fields = [];
        $disabled_fields = [];
        foreach ($fields as $field){
            if ($app->user->checkAccess("update_group_setting", ["property" => $field]))
                $show_fields[] = $field;
            else
                $disabled_fields[] = $field;
        }

        // Load validator rules
        $schema = new \Fortress\RequestSchema($app->config('schema.path') . "/forms/group-create.json");
        $app->jsValidator->setSchema($schema);

        $app->render($template, [
            "box_id" => $get['box_id'],
            "box_title" => "New Group",
            "submit_button" => "Create group",
            "form_action" => $app->site->uri['public'] . "/groups",
            "group" => $group,
            "themes" => $theme_list,
            "fields" => [
                "disabled" => $disabled_fields,
                "hidden" => []
            ],
            "buttons" => [
                "hidden" => [
                    "edit", "delete"
                ]
            ],
            "validators" => $app->jsValidator->rules()
        ]);
    });        
    
    /**
     * Renders the form for editing an existing group.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * The form can be rendered in "modal" (for popup) or "panel" mode, depending on the value of the GET parameter `render`.
     * Any fields that the user does not have permission to modify will be automatically disabled.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     * @param int $group_id the id of the group to edit.
     */
    $app->get('/forms/groups/g/:group_id/?', function ($group_id) use ($app) {
        // Access-controlled resource
        if (!$app->user->checkAccess('uri_groups')){
            $app->notFound();
        }

        $get = $app->request->get();

        if (isset($get['render']))
            $render = $get['render'];
        else
            $render = "modal";

        // Get the group to edit
        $group = UF\Group::find($group_id);

        // Get a list of all themes
        $theme_list = $app->site->getThemes();

        if ($render == "modal")
            $template = "components/common/group-info-modal.twig";
        else
            $template = "components/common/group-info-panel.twig";

        // Determine authorized fields
        $fields = ['name', 'new_user_title', 'landing_page', 'theme', 'is_default'];
        $show_fields = [];
        $disabled_fields = [];
        $hidden_fields = [];
        foreach ($fields as $field){
            if ($app->user->checkAccess("update_group_setting", ["property" => $field]))
                $show_fields[] = $field;
            else if ($app->user->checkAccess("view_group_setting", ["property" => $field]))
                $disabled_fields[] = $field;
            else
                $hidden_fields[] = $field;
        }

        // Load validator rules
        $schema = new \Fortress\RequestSchema($app->config('schema.path') . "/forms/group-update.json");
        $app->jsValidator->setSchema($schema);

        $app->render($template, [
            "box_id" => $get['box_id'],
            "box_title" => "Edit Group",
            "submit_button" => "Update group",
            "form_action" => $app->site->uri['public'] . "/groups/g/$group_id",
            "group" => $group,
            "themes" => $theme_list,
            "fields" => [
                "disabled" => $disabled_fields,
                "hidden" => $hidden_fields
            ],
            "buttons" => [
                "hidden" => [
                    "edit", "delete"
                ]
            ],
            "validators" => $app->jsValidator->rules()
        ]);
    });       
    
    /**
     * Renders the group listing page.
     *
     * This page renders a table of user groups, with dropdown menus for modifying those groups.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     */
    $app->get('/groups/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_groups')){
            $app->notFound();
        }
        
        $groups = UF\Group::queryBuilder()->get();
        
        $app->render('groups/groups.twig', [
            "groups" => $groups
        ]);
    }); 
    
    /**
     * Processes the request to create a new group.
     *
     * Processes the request from the group creation form, checking that:
     * 1. The group name is not already in use;
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @see formGroupCreate
     */
    $app->post('/groups/?', function () use ($app) {
        $post = $app->request->post();

        // DEBUG: view posted data
        //error_log(print_r($post, true));

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($app->config('schema.path') . "/forms/group-create.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Access-controlled resource
        if (!$app->user->checkAccess('create_group')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
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

        // Perform desired data transformations on required fields.
        $data['name'] = trim($data['name']);
        $data['new_user_title'] = trim($data['new_user_title']);
        $data['landing_page'] = strtolower(trim($data['landing_page']));
        $data['theme'] = trim($data['theme']);
        $data['can_delete'] = 1;

        // Check if group name already exists
        if (UF\Group::where('name', $data['name'])->first()){
            $ms->addMessageTranslated("danger", "GROUP_NAME_IN_USE", $post);
            $error = true;
        }

        // Halt on any validation errors
        if ($error) {
            $app->halt(400);
        }

        // Set default values if not specified or not authorized
        if (!isset($data['theme']) || !$app->user->checkAccess("update_group_setting", ["property" => "theme"]))
            $data['theme'] = "default";

        if (!isset($data['new_user_title']) || !$app->user->checkAccess("update_group_setting", ["property" => "new_user_title"])) {
            // Set default title for new users
            $data['new_user_title'] = "New User";
        }

        if (!isset($data['landing_page']) || !$app->user->checkAccess("update_group_setting", ["property" => "landing_page"])) {
            $data['landing_page'] = "dashboard";
        }

        if (!isset($data['icon']) || !$app->user->checkAccess("update_group_setting", ["property" => "icon"])) {
            $data['icon'] = "fa fa-user";
        }

        if (!isset($data['is_default']) || !$app->user->checkAccess("update_group_setting", ["property" => "is_default"])) {
            $data['is_default'] = "0";
        }

        // Create the group
        $group = new UF\Group($data);

        // Store new group to database
        $group->store();

        // Success message
        $ms->addMessageTranslated("success", "GROUP_CREATION_SUCCESSFUL", $data);
    });
    
    /**
     * Processes the request to update an existing group's details.
     *
     * Processes the request from the group update form, checking that:
     * 1. The group name is not already in use;
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @param int $group_id the id of the group to edit.
     * @see formGroupEdit
     */
    $app->post('/groups/g/:group_id/?', function ($group_id) use ($app) {
        $post = $app->request->post();

        // DEBUG: view posted data
        //error_log(print_r($post, true));

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($app->config('schema.path') . "/forms/group-update.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Get the target group
        $group = UF\Group::find($group_id);

        // If desired, put route-level authorization check here

        // Remove csrf_token
        unset($post['csrf_token']);

        // Check authorization for submitted fields, if the value has been changed
        foreach ($post as $name => $value) {
            if (isset($group->$name) && $post[$name] != $group->$name){
                // Check authorization
                if (!$app->user->checkAccess('update_group_setting', ['group' => $group, 'property' => $name])){
                    $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                    $app->halt(403);
                }
            } else if (!isset($group->$name)) {
                $ms->addMessageTranslated("danger", "NO_DATA");
                $app->halt(400);
            }
        }

        // Check that name is not already in use
        if (isset($post['name']) && $post['name'] != $group->name && UF\Group::where('name', $post['name'])->first()){
            $ms->addMessageTranslated("danger", "GROUP_NAME_IN_USE", $post);
            $app->halt(400);
        }

        // TODO: validate landing page route, theme, icon?

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        // Sanitize
        $rf->sanitize();

        // Validate, and halt on validation errors.
        if (!$rf->validate()) {
            $app->halt(400);
        }

        // Get the filtered data
        $data = $rf->data();

        // Update the group and generate success messages
        foreach ($data as $name => $value){
            if ($value != $group->$name){
                $group->$name = $value;
                // Add any custom success messages here
            }
        }

        $ms->addMessageTranslated("success", "GROUP_UPDATE", ["name" => $group->name]);
        $group->store();
    });       

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
    $app->post('/groups/g/:group_id/delete/?', function ($group_id) use ($app) {
        $post = $app->request->post();

        // Get the target group
        $group = UF\Group::find($group_id);

        // Get the alert message stream
        $ms = $app->alerts;

        // Check authorization
        if (!$app->user->checkAccess('delete_group', ['group' => $group])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
        }

        // Check that we are allowed to delete this group
        if ($group->can_delete == "0"){
            $ms->addMessageTranslated("danger", "CANNOT_DELETE_GROUP", ["name" => $group->name]);
            $app->halt(403);
        }

        // Do not allow deletion if this group is currently set as the default primary group
        if ($group->is_default == GROUP_DEFAULT_PRIMARY){
            $ms->addMessageTranslated("danger", "GROUP_CANNOT_DELETE_DEFAULT_PRIMARY", ["name" => $group->name]);
            $app->halt(403);
        }

        $ms->addMessageTranslated("success", "GROUP_DELETION_SUCCESSFUL", ["name" => $group->name]);
        $group->delete();       // TODO: implement Group function
        unset($group);
    });
