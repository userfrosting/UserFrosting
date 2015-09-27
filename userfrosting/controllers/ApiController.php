<?php

namespace UserFrosting;

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
        
        $get = $this->_app->request->get();
        
        $size = isset($get['size']) ? $get['size'] : 10;
        $page = isset($get['page']) ? $get['page'] : 0;
        $sort_field = isset($get['sort_field']) ? $get['sort_field'] : "user_name";
        $sort_order = isset($get['sort_order']) ? $get['sort_order'] : "asc";
        $filters = isset($get['filters']) ? $get['filters'] : [];
        
        $offset = $size*$page;
        
        // Count unpaginated total
        $total = $userQuery->count();
        
        // Exclude fields
        $userQuery = $userQuery
                ->exclude(['password', 'activation_token', 'last_activation_request', 'lost_password_request', 'lost_password_timestamp']);
                
        // Apply filters    
        foreach ($filters as $name => $value){
            $userQuery = $userQuery->where($name, 'LIKE', "%$value%");
        }
        
        // Count filtered total
        $total_filtered = $userQuery->count();
        
        // Paginate and sort
        $userQuery = $userQuery
                ->skip($offset)
                ->take($size)
                ->orderBy($sort_field, $sort_order);
        
        $result = [
            "count" => $total,
            "rows" => $userQuery->get(),
            "count_filtered" => $total_filtered
        ];
        
        echo json_encode($result);
    }
}
