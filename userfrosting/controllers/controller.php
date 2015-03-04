<?php

use \UserFrosting as UF;

class BaseController {

    protected $_app =         null; // The framework app to use (default Slim)
    protected $_page_schema = null; // The page schema

    public function __construct($app){
        $this->_app = $app;
    
        // Load a page schema.  You may override this in individual pages.
        $this->_page_schema = UF\PageSchema::load("default", $this->_app->config('schema.path') . "/pages/pages.json");
    }
    
    /* Renders the 404 error page.
    */
    public function page404(){
        $this->_app->render('pages/public/404.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "404 Error",
                'description' =>    "We couldn't deliver.  We're sorry.",
                'schema' =>         $this->_page_schema
            ]
        ]);
    }
    
    /* Get flash messages. */
    public function getMessages(){
        if ($this->_app->messages){
            echo json_encode($this->_app->messages->messages());
            
            // Reset alerts after they have been delivered
            $this->_app->messages->resetMessageStream();
        }
    }    
}


?>
