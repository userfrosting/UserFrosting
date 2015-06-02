<?php

namespace UserFrosting;

/*******

/groups/*

*******/

// Handles group-related activities
class GroupController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
        
        // Load account pages schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("user", $this->_app->config('schema.path') . "/pages/pages.json");
    }
    
    public function pageGroups(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_groups')){
            $this->_app->notFound();
        }
        
        $groups = GroupLoader::fetchAll();
        
        $this->_app->render('groups.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Groups",
                'description' =>    "Group management, authorization rules, add/remove groups, etc.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema
            ],
            "groups" => $groups
        ]);          
    }    
}
