<?php
/**
 * USAGE
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\DatabaseCheck());
 *
 */
namespace Slim\Extras\Middleware;

class DatabaseCheck extends \Slim\Middleware
{

    /**
     * Call middleware.
     *
     * @return void
     */
    public function call() 
    {
        // Attach as hook.
        $this->app->hook('slim.before', array($this, 'check'));
            
        // Call next middleware.
        $this->next->call();
    }

    public function check() {
        error_log($this->app->request->getPath());
        if ($this->app->request->getPath() != $this->app->urlFor('uri_install')){
            // Test database connection
            try {
                \UserFrosting\Database::connection();
            } catch (\PDOException $e){
                $this->app->redirect($this->app->urlFor('uri_install'));
            }
        }
    }
}