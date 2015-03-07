<?php

namespace UserFrosting;

class BaseController {

    protected $_app =         null; // The framework app to use (default Slim)
    protected $_page_schema = null; // The page schema

    public function __construct($app){
        $this->_app = $app;
    
        // Load a page schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("default", $this->_app->config('schema.path') . "/pages/pages.json");
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
    
    /* Render a JS file containing client-side configuration data (paths, etc)
    */
    public function configJS(){
        $this->_app->response->headers->set("Content-Type", "application/javascript");
        $this->_app->response->setBody("var userfrosting = " . json_encode(
            [
                "uri" => [
                    "public" => $this->_app->userfrosting['uri']['public']
                ]
            ]
        ));
    }
    
    
    /* Get flash alerts and reset message stream. */
    public function alerts(){
        if ($this->_app->alerts){
            echo json_encode($this->_app->alerts->getAndClearMessages());
        }
    }    
}


?>
