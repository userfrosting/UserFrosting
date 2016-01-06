<?php

namespace UserFrosting;

/**
 * AuthController Class
 *
 * Controller class for /auth/* URLs.  Handles auth-related activities, including editing/adding/deleting auth rules
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class AuthController extends \UserFrosting\BaseController {

    /**
     * Create a new AuthController object.
     *
     * @param UserFrosting $app The main UserFrosting app.
     */
    public function __construct($app){
        $this->_app = $app;
    }

    /**
     * Renders the form for creating a new auth rule.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     */
    public function formAuthCreate($id, $type = "group"){
        // Access-controlled resource
        if (!$this->_app->user->checkAccess('create_auth')){
            $this->_app->notFound();
        }

        $get = $this->_app->request->get();

        // Load validator rules
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/auth-create.json");
        $this->_app->jsValidator->setSchema($schema);

        // Get the group for which we are creating this new rule
        $group = Group::find($id);

        $this->_app->render("components/common/auth-info-form.twig", [
            "box_id" => $get['box_id'],
            "box_title" => "New Authorization Rule for Group '{$group->name}'",
            "subtext" => "This rule will apply to any user who is a member of group '{$group->name}'.",
            "submit_button" => "Create rule",
            "form_action" => $this->_app->site->uri['public'] . "/groups/g/$id/auth",
            "validators" => $this->_app->jsValidator->rules()
        ]);
    }

    /**
     * Renders the form for editing an existing auth rule.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication (and should generally be limited to admins or the root user).
     * Request type: GET
     * @param int $rule_id the id of the rule to edit.
     */
    public function formAuthEdit($rule_id){
        // Access-controlled resource
        if (!$this->_app->user->checkAccess('update_auth')){
            $this->_app->notFound();
        }

        $get = $this->_app->request->get();

        // Get the rule to edit
        $rule = GroupAuth::find($rule_id);

        // Get the group for which we are creating this new rule
        $group = Group::find($rule->group_id);

        // Load validator rules
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/auth-update.json");
        $this->_app->jsValidator->setSchema($schema);

        $this->_app->render("components/common/auth-info-form.twig", [
            "box_id" => $get['box_id'],
            "box_title" => "Edit Authorization Rule for Group '{$group->name}'",
            "subtext" => "This rule will apply to any user who is a member of group '{$group->name}'.",
            "submit_button" => "Update rule",
            "form_action" => $this->_app->site->uri['public'] . "/groups/auth/a/$rule_id",
            "fields" => [
                "disabled" => ['hook'],
                "hidden" => []
            ],
            "rule" => $rule,
            "validators" => $this->_app->jsValidator->rules()
        ]);
    }

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
    public function createAuthRule($id, $type="group"){
        $post = $this->_app->request->post();

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/auth-create.json");

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // TODO: Check that the group exists
        $group = Group::find($id);

        // Access-controlled resource
        if (!$this->_app->user->checkAccess('create_auth', ['group' => $group])){
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

        // Perform desired data transformations on required fields.
        $data['hook'] = trim($data['hook']);
        $data['conditions'] = trim($data['conditions']);

        // Check if the group already has a rule for this hook
        if (GroupAuth::where("group_id", $id)->where("hook", $data['hook'])->first()){
            $post['name'] = $group->name;
            $ms->addMessageTranslated("danger", "GROUP_AUTH_EXISTS", $post);
            $this->_app->halt(400);
        }

        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }

        // Create the rule
        $rule = new GroupAuth();
        $rule->group_id = $id;
        $rule->hook = $data['hook'];
        $rule->conditions = $data['conditions'];

        // Store new group to database
        $rule->save();

        // Success message
        $data['name'] = $group['name'];
        $ms->addMessageTranslated("success", "GROUP_AUTH_CREATION_SUCCESSFUL", $data);
    }

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
    public function updateAuthRule($rule_id){
        $post = $this->_app->request->post();

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/auth-update.json");

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Get the target group auth rule
        $rule = GroupAuth::find($rule_id);

        // Access-controlled resource
        if (!$this->_app->user->checkAccess('update_auth', ['rule' => $rule])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }

        // Remove csrf_token
        unset($post['csrf_token']);

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        // Sanitize
        $rf->sanitize();

        // Validate, and halt on validation errors.
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }

        // Get the filtered data
        $data = $rf->data();

        // Update the rule.  TODO: check that conditions are well-formed?
        $rule->conditions = $data['conditions'];

        // Store new group to database
        $rule->save();

        // Get group and generate success messages
        $group = Group::find($rule->group_id);
        $ms->addMessageTranslated("success", "GROUP_AUTH_UPDATE_SUCCESSFUL", ["name" => $group->name, "hook" => $rule->hook]);
    }

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
    public function deleteAuthRule($auth_id){
        $post = $this->_app->request->post();

        // Get the target rule
        $rule = GroupAuth::find($auth_id);

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Check authorization
        if (!$this->_app->user->checkAccess('delete_auth', ['rule' => $rule])){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }

        // Get group and generate success messages
        $group = Group::find($rule->group_id);
        $ms->addMessageTranslated("success", "GROUP_AUTH_DELETION_SUCCESSFUL", ["name" => $group->name, "hook" => $rule->hook]);
        $rule->delete();
        unset($rule);
    }

}
