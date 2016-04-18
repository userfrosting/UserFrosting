<?php
    
    /**
     * Routes for /auth/* URLs.  Handles auth-related activities, including editing/adding/deleting auth rules
     *
     * @package UserFrosting
     * @author Alex Weissman
     */
     
    use UserFrosting as UF;
    
    global $app;   

    /**
     * Renders the form for editing an existing auth rule.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     * @param int $rule_id the id of the rule to edit.
     */
    $app->get('/forms/groups/auth/a/:rule_id/?', function ($rule_id) use ($app) {

        // Access-controlled resource
        if (!$app->user->checkAccess('update_auth')){
            $app->notFound();
        }
        
        $get = $app->request->get();
        
        // Get the rule to edit
        $rule = UF\GroupAuth::find($rule_id);
        
        // Get the group for which we are creating this new rule
        $group = UF\Group::find($rule->group_id);
        
        // Load validator rules
        $schema = $app->loadRequestSchema("forms/auth-update.json");
        $app->jsValidator->setSchema($schema);
        
        $app->render("components/common/auth-info-form.twig", [
            "box_id" => $get['box_id'],
            "box_title" => "Edit Authorization Rule for Group '{$group->name}'",
            "subtext" => "This rule will apply to any user who is a member of group '{$group->name}'.",
            "submit_button" => "Update rule",
            "form_action" => $app->site->uri['public'] . "/groups/auth/a/$rule_id",
            "fields" => [
                "disabled" => ['hook'],
                "hidden" => []
            ],
            "rule" => $rule,
            "validators" => $app->jsValidator->rules()
        ]);
    });
    
    /**
     * Renders the form for creating a new auth rule.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     */
    $app->get('/forms/groups/g/:group_id/auth/?', function ($group_id) use ($app) {
        // Access-controlled resource
        if (!$app->user->checkAccess('create_auth')){
            $app->notFound();
        }

        $get = $app->request->get();

        // Load validator rules
        $schema = $app->loadRequestSchema("forms/auth-create.json");
        $app->jsValidator->setSchema($schema);

        // Get the group for which we are creating this new rule
        $group = UF\Group::find($id);

        $app->render("components/common/auth-info-form.twig", [
            "box_id" => $get['box_id'],
            "box_title" => "New Authorization Rule for Group '{$group->name}'",
            "subtext" => "This rule will apply to any user who is a member of group '{$group->name}'.",
            "submit_button" => "Create rule",
            "form_action" => $app->site->uri['public'] . "/groups/g/$id/auth",
            "validators" => $app->jsValidator->rules()
        ]);
    });   

    // List auth rules for a group
    $app->get('/groups/g/:group_id/auth?', function ($group_id) use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_authorization_settings')){
            $app->notFound();
        }

        $group = UF\Group::find($group_id);

        // Load all auth rules
        $rules = UF\GroupAuth::where('group_id', $group_id)->get();

        $app->render('config/authorization.twig', [
            "group" => $group,
            "rules" => $rules
        ]);
    })->name('uri_authorization');  

    /**
     * Processes the request to delete an existing group auth rule.
     *
     * Deletes the specified auth rule.
     * Before doing so, checks that:
     * 1. The user has permission to delete auth rules.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @param int $auth_id the id of the group auth rule to delete.
     * @todo make this work for user-level rules as well
     */
    $app->post('/auth/a/:rule_id/delete/?', function ($rule_id) use ($app) {
        $post = $app->request->post();

        // Get the target rule
        $rule = UF\GroupAuth::find($auth_id);

        // Get the alert message stream
        $ms = $app->alerts;

        // Check authorization
        if (!$app->user->checkAccess('delete_auth', ['rule' => $rule])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
        }

        // Get group and generate success messages
        $group = UF\Group::find($rule->group_id);
        $ms->addMessageTranslated("success", "GROUP_AUTH_DELETION_SUCCESSFUL", ["name" => $group->name, "hook" => $rule->hook]);
        $rule->delete();
        unset($rule);
    });  
    
    /**
     * Processes the request to update an existing group authorization rule.
     *
     * Processes the request from the auth update form, checking that:
     * 1. The user has the necessary permissions to update the posted field(s);
     * 2. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @param int $rule_id the id of the group auth rule to edit.
     * @see formAuthEdit
     * @todo make this work for user-level rules as well
     */
    $app->post('/groups/auth/a/:rule_id?', function ($rule_id) use ($app) {
        $post = $app->request->post();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/auth-update.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Get the target group auth rule
        $rule = UF\GroupAuth::find($rule_id);

        // Access-controlled resource
        if (!$app->user->checkAccess('update_auth', ['rule' => $rule])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
        }

        // Remove csrf_token
        unset($post['csrf_token']);

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

        // Update the rule.  TODO: check that conditions are well-formed?
        $rule->conditions = $data['conditions'];

        // Store new group to database
        $rule->save();

        // Get group and generate success messages
        $group = UF\Group::find($rule->group_id);
        $ms->addMessageTranslated("success", "GROUP_AUTH_UPDATE_SUCCESSFUL", ["name" => $group->name, "hook" => $rule->hook]);
    });   

    /**
     * Processes the request to create a new auth rule.
     *
     * Processes the request from the auth creation form, checking that:
     * 1. The group does not already have a rule for the specified hook.
     * 2. The user has the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication (and should generally be limited to admins or the root user).
     * Request type: POST
     * @see formAuthCreate
     * @todo make this work for user-level rules as well
     */
    $app->post('/groups/g/:group_id/auth/?', function ($group_id) use ($app) {
        $post = $app->request->post();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/auth-create.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // TODO: Check that the group exists
        $group = UF\Group::find($id);

        // Access-controlled resource
        if (!$app->user->checkAccess('create_auth', ['group' => $group])){
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
        $data['hook'] = trim($data['hook']);
        $data['conditions'] = trim($data['conditions']);

        // Check if the group already has a rule for this hook
        if (UF\GroupAuth::where("group_id", $id)->where("hook", $data['hook'])->first()){
            $post['name'] = $group->name;
            $ms->addMessageTranslated("danger", "GROUP_AUTH_EXISTS", $post);
            $app->halt(400);
        }

        // Halt on any validation errors
        if ($error) {
            $app->halt(400);
        }

        // Create the rule
        $rule = new UF\GroupAuth();
        $rule->group_id = $id;
        $rule->hook = $data['hook'];
        $rule->conditions = $data['conditions'];

        // Store new group to database
        $rule->save();

        // Success message
        $data['name'] = $group['name'];
        $ms->addMessageTranslated("success", "GROUP_AUTH_CREATION_SUCCESSFUL", $data);
    });
    