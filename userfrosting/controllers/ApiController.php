<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * ApiController Class
 *
 * Controller class for /api/* URLs.  Handles all api requests.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class ApiController extends \UserFrosting\BaseController {

    /**
     * Create a new ApiController object.
     *
     * @param UserFrosting $app The main UserFrosting app.
     */
    public function __construct($app){
        $this->_app = $app;
    }
    
    /**
     * Returns a list of Users
     *
     * Generates a list of users, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     * @param string $primary_group_name optional.  If specified, will only display users in that particular primary group.
     * @todo implement interface to modify user-assigned authorization hooks and permissions
     */        
    public function listUsers($page = 0, $size = 10, $primary_group_name = null){
        $get = $this->_app->request->get();
                
        $size = isset($get['size']) ? $get['size'] : 10;
        $page = isset($get['page']) ? $get['page'] : 0;
        $sort_field = isset($get['sort_field']) ? $get['sort_field'] : "user_name";
        $sort_order = isset($get['sort_order']) ? $get['sort_order'] : "asc";
        $filters = isset($get['filters']) ? $get['filters'] : [];
        $primary_group_name = isset($get['primary_group']) ? $get['primary_group'] : null;
        
        $offset = $size*$page;                
                
        // Optional filtering by primary group
        if ($primary_group_name){
            $primary_group = Group::where('name', $primary_group_name)->first();
            
            if (!$primary_group)
                $this->_app->notFound();
            
            // Access-controlled page
            if (!$this->_app->user->checkAccess('uri_group_users', ['primary_group_id' => $primary_group->id])){
                $this->_app->notFound();
            }
            
            $userQuery = new User;
            $userQuery = $userQuery->where('primary_group_id', $primary_group->id);

        } else {
            // Access-controlled page
            if (!$this->_app->user->checkAccess('uri_users')){
                $this->_app->notFound();
            }
            
            $userQuery = new User;
        }
                
        // Count unpaginated total
        $total = $userQuery->count();
            
        // Exclude fields
        $userQuery = $userQuery
                ->exclude(['password', 'secret_token']);
        
        Capsule::connection()->enableQueryLog();
        
        
        // Get unfiltered, unsorted, unpaginated collection
        $user_collection = $userQuery->get();
        
        // Load recent events for all users and merge into the collection.  This can't be done in one query,
        // at least not efficiently.  See http://laravel.io/forum/04-05-2014-eloquent-eager-loading-to-limit-for-each-post
        $last_sign_in_times = $user_collection->getRecentEvents('sign_in');
        $last_sign_up_times = $user_collection->getRecentEvents('sign_up', 'sign_up_time');
        
        // Apply filters        
        foreach ($filters as $name => $value){
            // For date filters, search for weekday, month, or year
            if ($name == 'last_sign_in_time') {
                $user_collection = $user_collection->filterRecentEventTime('sign_in', $last_sign_in_times, $value);
            } else if ($name == 'sign_up_time') {
                $user_collection = $user_collection->filterRecentEventTime('sign_up', $last_sign_up_times, $value);
            } else {
                $user_collection = $user_collection->filterTextField($name, $value);
            }
        }
        
        // Count filtered results
        $total_filtered = count($user_collection);
        
        // Paginate and sort
        if ($sort_order == "desc")
            $user_collection = $user_collection->sortByDesc($sort_field);
        else        
            $user_collection = $user_collection->sortBy($sort_field);
                    
        $user_collection = $user_collection->slice($offset, $size);     
                
        $result = [
            "count" => $total,
            "rows" => $user_collection->values()->toArray(),
            "count_filtered" => $total_filtered
        ];
        
        $query = Capsule::getQueryLog();
        //print_r($query);        
        
        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        $this->_app->response->headers->set('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}
