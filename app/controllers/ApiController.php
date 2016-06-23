<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \League\Csv\Writer;

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
     */
    public function listUsers(){
        $get = $this->_app->request->get();
        
        $filters = isset($get['filters']) ? $get['filters'] : [];
        $primary_group_name = isset($get['primary_group']) ? $get['primary_group'] : null;

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
                $user_collection = $user_collection->filterRecentEventTime('sign_up', $last_sign_up_times, $value, "Unknown");
            } else {
                $user_collection = $user_collection->filterTextField($name, $value);
            }
        }
        
        // Render
        $this->sortPaginateRender($user_collection, $total, 'users');
    }
    
    /**
     * Sorts, paginates, and renders a collection according to the GET parameters (so, JSON or CSV)
     *
     * @param Collection $collection The Eloquent collection to be rendered.
     * @param int $total The total number of records in the collection, before pagination and filtering.
     * @param string $csv_type A string representing the object type, to be added to the filename when generating a CSV.
     * @param callable $csv_callback A callback to apply to the collection before rendering as CSV.  Useful for flattening data.
     */
    private function sortPaginateRender($collection, $total, $csv_type, $csv_callback = null)
    {
        $get = $this->_app->request->get();
        
        $size = isset($get['size']) ? $get['size'] : null;
        $page = isset($get['page']) ? $get['page'] : null;
        $sort_field = isset($get['sort_field']) ? $get['sort_field'] : "user_name";
        $sort_order = isset($get['sort_order']) ? $get['sort_order'] : "asc";
        
        // Count filtered results
        $total_filtered = count($collection);
        
        // Sort
        if ($sort_order == "desc")
            $collection = $collection->sortByDesc($sort_field, SORT_NATURAL|SORT_FLAG_CASE);
        else        
            $collection = $collection->sortBy($sort_field, SORT_NATURAL|SORT_FLAG_CASE);
            
        // Paginate
        if ( ($page !== null) && ($size !== null) ){
            $offset = $size*$page;
            $collection = $collection->slice($offset, $size);
        }
        
        $this->render($collection, $total, $total_filtered, $csv_type, $csv_callback);
    }
    
    /**
     * Renders a collection according to the GET parameters (so, JSON or CSV)
     *
     * @param Collection $collection The Eloquent collection to be rendered.
     * @param int $total The total number of records in the collection, before pagination and filtering.
     * @param int $total_filtered The total number of records in the collection, after filtering (but before pagination).
     * @param string $csv_type A string representing the object type, to be added to the filename when generating a CSV.
     * @param callable $csv_callback A callback to apply to the collection before rendering as CSV.  Useful for flattening data.
     */    
    private function render($collection, $total, $total_filtered, $csv_type, $csv_callback = null)
    {
        $get = $this->_app->request->get();
        $format = isset($get['format']) ? $get['format'] : "json";
    
        if ($format == "csv"){
            $settings = http_build_query($get);
            $date = date("Ymd");
            $this->_app->response->headers->set('Content-Disposition', "attachment;filename=$date-$csv_type-$settings.csv");
            $this->_app->response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            
            if ($csv_callback){
                $collection = $csv_callback($collection);
            }
            
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $columnNames = array_keys($collection->values()->toArray()[0]);
            $csv->insertOne($columnNames);
            $collection->each(function ($item) use ($csv) {
                $csv->insertOne($item->getAttributes());
            });
            
            echo (string) $csv;
        } else {
            $result = [
                "count" => $total,
                "rows" => $collection->values()->toArray(),
                "count_filtered" => $total_filtered
            ];
        
            // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
            // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
            $this->_app->response->headers->set('Content-Type', 'application/json; charset=utf-8');
            echo json_encode($result, JSON_PRETTY_PRINT);
        }
    }      
}
