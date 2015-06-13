<?php

namespace UserFrosting;

class BaseController {

    protected $_app =         null; // The framework app to use (default Slim)

    public function __construct($app){
        $this->_app = $app;
    }
    
    /* Renders the 404 error page.
    */
    public function page404(){
        $this->_app->render('common/404.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "404 Error",
                'description' =>    "We couldn't deliver.  We're sorry."
            ]
        ]);
    }

    /* Renders the database error page.
    */
    public function pageDatabaseError(){
        $this->_app->render('common/database.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Database Error",
                'description' =>    "There's something wrong. We can't connect to the database."
            ]
        ]);
    }
    
    /* Render a JS file containing client-side configuration data (paths, etc)
    */
    public function configJS(){
        $this->_app->response->headers->set("Content-Type", "application/javascript");
        $this->_app->response->setBody("var site = " . json_encode(
            [
                "uri" => [
                    "public" => $this->_app->site->uri['public']
                ],
                "debug" => $this->_app->config('debug')
            ]
        ));
    }
    
    /* Render theme CSS */
    public function themeCSS(){
        $this->_app->response->headers->set("Content-Type", "text/css");
        $css_include = $this->_app->config('themes.path') . "/" . $this->_app->user->getTheme() . "/css/theme.css";
        $this->_app->response->setBody(file_get_contents($css_include));
    }    
    
    /* Get flash alerts and reset message stream. */
    public function alerts(){
        if ($this->_app->alerts){
            echo json_encode($this->_app->alerts->getAndClearMessages());
        }
    }
}


?>
