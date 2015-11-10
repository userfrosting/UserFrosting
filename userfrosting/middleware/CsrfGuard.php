<?php
/**
 * CSRF Guard, modified from https://github.com/codeguy/Slim-Extras
 *
 * Use this middleware with your Slim Framework application
 * to protect you from CSRF attacks.
 *
 * USAGE
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\CsrfGuard());
 *
 */
namespace Slim\Extras\Middleware;

class CsrfGuard extends \Slim\Middleware
{
    /**
     * CSRF token key name.
     *
     * @var string
     */
    protected $key;

    /**
     * Constructor.
     *
     * @param string    $key        The CSRF token key name.
     * @return void
     */
    public function __construct($key = 'csrf_token')
    {
        if (! is_string($key) || empty($key) || preg_match('/[^a-zA-Z0-9\-\_]/', $key)) {
            throw new \OutOfBoundsException('Invalid CSRF token key "' . $key . '"');
        }

        $this->key = $key;
    }

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

    /**
     * Check CSRF token is valid.
     * Note: Also checks POST data to see if a Moneris RVAR CSRF token exists.
     *
     * @return void
     */
    public function check() {
        // Check sessions are enabled.
        if (session_id() === '') {
            throw new \Exception('Sessions are required to use the CSRF Guard middleware.');
        }

        if (! isset($_SESSION[$this->key])) {
			if (function_exists('openssl_random_pseudo_bytes')) {
				$rand_num = openssl_random_pseudo_bytes(16);//pull 16 bytes from /dev/random
			}else{
				/*
					RYO(Roll Your Own) random number gen.
					only used in the event openssl isn't available
				*/
				$rand = array();
				for($i = 0; $i < 64; $i++) {
					$random = mt_rand(rand(0,65012), mt_getrandmax());//get a random number between rand(0,65012) and mt rand max
					$rand[$i] = mt_rand($i, $random); //add an array key of $i and a value of a number between $i and the first random number
				}
				$rand = array_sum($rand); //shuffle the random number, then sum the values
				$rand_num = str_shuffle($rand * 64); //multiply the rand number by 64 and shuffle the string.
			}
			if(isset($rand_num)) {
				$build_string = $rand_num . serialize($_SERVER) . time();
				if(isset($build_string)) {
					$token = hash('whirlpool', str_shuffle($build_string));
				} else {
                    throw new \Exception('Could not generate a random number for the CSRF token!');
                }
			} else {
                throw new \Exception('Could not generate a random number for the CSRF token!');
            }
            $_SESSION[$this->key] = $token; //sha1(serialize($_SERVER) . rand(0, 0xffffffff));
        }

        $token = $_SESSION[$this->key];

        // Validate the CSRF token.
        if (in_array($this->app->request()->getMethod(), array('POST', 'PUT', 'DELETE'))) {
            $userToken = $this->app->request()->post($this->key);
            if ($token !== $userToken) {
                $this->app->alerts->addMessage('danger', 'Invalid or missing CSRF token.');
                $this->app->halt(400);
            }
        }

        // Assign CSRF token key and value to view.
        $this->app->view()->appendData(array(
            'csrf_key'      => $this->key,
            'csrf_token'    => $token,
        ));
    }
}