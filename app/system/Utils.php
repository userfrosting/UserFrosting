<?php

/**
 * Misc utilities.
 *
 */
 
namespace UserFrosting;
 
abstract class Utils {

    /**
     * Generates a new captcha for the user registration form.
     *
     * This generates a captcha as a base 64 string, to be placed inside the src attribute of an html image tag.
     * @author r3wt
     * @return string The captcha, encoded as a base 64 string.
     */
    public static function generateCaptcha(){

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
        imagestring($image,5, $x, 7, $security_code, $black);
        imagestring($image,5, $x, 7, $security_code, $black);
        //start ob
        ob_start();
        imagepng($image);

        //get binary image data
        $data = ob_get_clean();
        //return base64
        return 'data:image/png;base64,'.chunk_split(base64_encode($data)); //return the base64 encoded image.
    }
}
