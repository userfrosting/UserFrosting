<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use UserFrosting\Session\Session;

/**
 * Captcha Class
 *
 * Implements the captcha for user registration.
 *
 * @author r3wt
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#messages
 */
class Captcha
{
    /**
     * @var string The randomly generated captcha code.
     */
    protected $code;

    /**
     * @var string The captcha image, represented as a binary string.
     */
    protected $image;

    /**
     * @var Session We use the session object so that the hashed captcha token will automatically appear in the session.
     */
    protected $session;

    /**
     * @var string
     */
    protected $key;

    /**
     * Create a new captcha.
     *
     * @param Session $session
     * @param string  $key
     */
    public function __construct(Session $session, $key)
    {
        $this->session = $session;
        $this->key = $key;

        if (!$this->session->has($key)) {
            $this->session[$key] = [];
        }
    }

    /**
     * Generates a new captcha for the user registration form.
     *
     * This generates a random 5-character captcha and stores it in the session with an md5 hash.
     * Also, generates the corresponding captcha image.
     */
    public function generateRandomCode()
    {
        $md5_hash = md5(rand(0, 99999));
        $this->code = substr($md5_hash, 25, 5);
        $enc = md5($this->code);

        // Store the generated captcha value to the session
        $this->session[$this->key] = $enc;

        $this->generateImage();
    }

    /**
     * Returns the captcha code.
     *
     * @return string
     */
    public function getCaptcha()
    {
        return $this->code;
    }

    /**
     * Returns the captcha image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check that the specified code, when hashed, matches the code in the session.
     * Also, stores the specified code in the session with an md5 hash.
     *
     * @param  string $code
     * @return bool
     */
    public function verifyCode($code)
    {
        return md5($code) == $this->session[$this->key];
    }

    /**
     * Generate the image for the current captcha.
     * This generates an image as a binary string.
     *
     * @return string
     */
    protected function generateImage()
    {
        $width = 150;
        $height = 30;

        $image = imagecreatetruecolor(150, 30);

        //color pallette
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $yellow = imagecolorallocate($image, 255, 255, 0);
        $dark_grey = imagecolorallocate($image, 64, 64, 64);
        $blue = imagecolorallocate($image, 0, 0, 255);

        //create white rectangle
        imagefilledrectangle($image, 0, 0, 150, 30, $white);

        //add some lines
        for ($i = 0; $i < 2; $i++) {
            imageline($image, 0, rand() % 10, 10, rand() % 30, $dark_grey);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $red);
            imageline($image, 0, rand() % 30, 150, rand() % 30, $yellow);
        }

        // RandTab color pallette
        $randc[0] = imagecolorallocate($image, 0, 0, 0);
        $randc[1] = imagecolorallocate($image, 255, 0, 0);
        $randc[2] = imagecolorallocate($image, 255, 255, 0);
        $randc[3] = imagecolorallocate($image, 64, 64, 64);
        $randc[4] = imagecolorallocate($image, 0, 0, 255);

        //add some dots
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $randc[rand() % 5]);
        }

        //calculate center of text
        $x = (150 - 0 - imagefontwidth(5) * strlen($this->code)) / 2 + 0 + 5;

        //write string twice
        imagestring($image, 5, $x, 7, $this->code, $black);
        imagestring($image, 5, $x, 7, $this->code, $black);
        //start ob
        ob_start();
        imagepng($image);

        //get binary image data
        $this->image = ob_get_clean();

        return $this->image;
    }
}
