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
    
    /*
    generates a base 64 string to be placed inside the src attribute of an html image tag.
    @blame -r3wt
    */    
    public function generateCaptcha(){
    
        $md5_hash = md5(rand(0,99999));
        $security_code = substr($md5_hash, 25, 5);
        $enc = md5($security_code);
        // Store the generated captcha to the session
        $_SESSION['userfrosting']['captcha'] = $enc;
    
        $width = 150;
        $height = 30;
    
        $image = imagecreatetruecolor(150, 30);
    
        //color pallette
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image,255,0,0);
        $yellow = imagecolorallocate($image, 255, 255, 0);
        $dark_grey = imagecolorallocate($image, 64,64,64);
        $blue = imagecolorallocate($image, 0,0,255);
    
        //create white rectangle
        imagefilledrectangle($image,0,0,150,30,$white);
    
        //add some lines
        for($i=0;$i<2;$i++) {
            imageline($image,0,rand()%10,10,rand()%30,$dark_grey);
            imageline($image,0,rand()%30,150,rand()%30,$red);
            imageline($image,0,rand()%30,150,rand()%30,$yellow);
}

        // RandTab color pallette
        $randc[0] = imagecolorallocate($image, 0, 0, 0);
        $randc[1] = imagecolorallocate($image,255,0,0);
        $randc[2] = imagecolorallocate($image, 255, 255, 0);
        $randc[3] = imagecolorallocate($image, 64,64,64);
        $randc[4] = imagecolorallocate($image, 0,0,255);

        //add some dots
        for($i=0;$i<1000;$i++) {
            imagesetpixel($image,rand()%200,rand()%50,$randc[rand()%5]);
        }    
        
        //calculate center of text
        $x = ( 150 - 0 - imagefontwidth( 5 ) * strlen( $security_code ) ) / 2 + 0 + 5;
    
        //write string twice
        ImageString($image,5, $x, 7, $security_code, $black);
        ImageString($image,5, $x, 7, $security_code, $black);
        //start ob
        ob_start();
        ImagePng($image);
    
        //get binary image data
        $data = ob_get_clean();
        //return base64
        return 'data:image/png;base64,'.chunk_split(base64_encode($data)); //return the base64 encoded image.
    }
    
}


?>
