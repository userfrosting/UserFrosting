<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Util;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;

class CheckEnvironment
{

    protected $view;
    
    protected $locator;

    protected $results = array();

    protected $check;

    public function __construct($view, $locator)
    {
        $this->view = $view;
        $this->locator = $locator;
    }
    
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $problemsFound = $this->checkAll();
    
        if ($problemsFound){
            $response = $this->view->render($response, 'pages/config-errors.html.twig', [
                "messages" => $this->results
            ]);
        } else {
            $response = $next($request, $response);
        }
        
        return $response;
    }
    
    public function checkAll()
    {
        $problemsFound = false;
        
        if ($this->checkApache()) $problemsFound = true;
        
        if ($this->checkPhp()) $problemsFound = true;
        
        if ($this->checkPdo()) $problemsFound = true;
        
        if ($this->checkGd()) $problemsFound = true;        
        
        if ($this->checkImageFunctions()) $problemsFound = true;
        
        if ($this->checkPermissions()) $problemsFound = true;
        
        return $problemsFound;
    }
    
    public function checkApache()
    {
        $problemsFound = false;
        
        // Perform some Apache checks.  We may also need to do this before any routing takes place.
        if (strpos(php_sapi_name(), 'apache') !== false) {

            $require_apache_modules = ['mod_rewrite'];
            $apache_modules = apache_get_modules();

            $apache_status = [];

            foreach ($require_apache_modules as $module) {
                if (!in_array($module, $apache_modules)) {
                    $problemsFound = true;
                    $this->results['apache-' . $module] = [
                        "title" => "<i class='fa fa-server fa-fw'></i> Missing Apache module <b>$module</b>.",
                        "message" => "Please make sure that the <code>$module</code> Apache module is installed and enabled.  If you use shared hosting, you will need to ask your web host to do this for you.",
                        "success" => false
                    ];
                } else {
                    $this->results['apache-' . $module] = [
                        "title" => "<i class='fa fa-server fa-fw'></i> Apache module <b>$module</b> is installed and enabled.",
                        "message" => "Great, we found the <code>$module</code> Apache module!",
                        "success" => true
                    ];
                }
            }
        }
        
        return $problemsFound;
    }
    
    public function checkPhp()
    {
        $problemsFound = false;
    
        // Check PHP version
        if (version_compare(phpversion(), \UserFrosting\PHP_MIN_VERSION, '<')) {
            $problemsFound = true;
            $this->results['phpVersion'] = [
                "title" => "<i class='fa fa-code fa-fw'></i> You need to upgrade your PHP installation.",
                "message" => "I'm sorry, UserFrosting requires version " . \UserFrosting\PHP_MIN_VERSION . " or greater.  Please upgrade your version of PHP, or contact your web hosting service and ask them to upgrade it for you.",
                "success" => false
            ];
        } else {
            $this->results['phpVersion'] = [
                "title" => "<i class='fa fa-code fa-fw'></i> PHP version checks out!",
                "message" => "You're using PHP " . \UserFrosting\PHP_MIN_VERSION . ".  Great!",
                "success" => true
            ];
        }
        
        return $problemsFound;
    }
    
    /**
     * Check that PDO is installed and enabled.
     */
    public function checkPdo()
    {
        $problemsFound = false;
        
        if (!class_exists('PDO')){
            $problemsFound = true;
            $this->results['pdo'] = [
                "title" => "<i class='fa fa-database fa-fw'></i> PDO is not installed.",
                "message" => "I'm sorry, you must have PDO installed and enabled in order for UserFrosting to access the database.  If you don't know what PDO is, please see <a href='http://php.net/manual/en/book.pdo.php'>http://php.net/manual/en/book.pdo.php</a>.",
                "success" => false
            ];
        } else {
            $this->results['pdo'] = [
                "title" => "<i class='fa fa-database fa-fw'></i> PDO is installed!",
                "message" => "You've got PDO installed.  Good job!",
                "success" => true
            ];
        }
        
        return $problemsFound;
    }
    
    /**
     * Check for GD library (required for Captcha).
     */
    public function checkGd()
    {
        $problemsFound = false;
        
        if (!(extension_loaded('gd') && function_exists('gd_info'))) {
            $problemsFound = true;
            $this->results['gd'] = [
                "title" => "<i class='fa fa-image fa-fw'></i> GD library not installed",
                "message" => "We could not confirm that the <code>GD</code> library is installed and enabled.  GD is an image processing library that UserFrosting uses to generate captcha codes for user account registration.",
                "success" => false
            ];
        } else {
            $this->results['gd'] = [
                "title" => "<i class='fa fa-image fa-fw'></i> GD library installed!",
                "message" => "Great, you have <code>GD</code> installed and enabled.",
                "success" => true
            ];
        }
        
        return $problemsFound;  
    }
    
    public function checkImageFunctions()
    {
        $problemsFound = false;
        
        $funcs = [
            'imagepng',
            'imagecreatetruecolor',
            'imagecolorallocate',
            'imagefilledrectangle',
            'imageline',
            'imagesetpixel',
            'imagefontwidth',
            'imagestring'
        ];
        
        foreach ($funcs as $func) {
            if (!function_exists($func)) {
                $problemsFound = true;
                $this->results['function-' . $func] = [
                    "title" => "<i class='fa fa-code fa-fw'></i> Missing image manipulation function.",
                    "message" => "It appears that function <code>$func</code> is not available.  UserFrosting needs this to render captchas.",
                    "success" => false
                ];
            } else {
                $this->results['function-' . $func] = [
                    "title" => "<i class='fa fa-code fa-fw'></i> Function <b>$func</b> is available!",
                    "message" => "Sweet!",
                    "success" => true
                ];
            }
        }
    
        return $problemsFound;
    }

    function checkPermissions()
    {
        $problemsFound = false;
    
        $shouldBeWriteable = [
            $this->locator->findResource('log://') => true,        
            $this->locator->findResource('cache://') => true,
            $this->locator->findResource('session://') => true,
            $this->locator->findResource('sprinkles://') => false,
            \UserFrosting\VENDOR_DIR => false
        ];    

        // Check for essential files & perms
        foreach ($shouldBeWriteable as $file => $assertWriteable) {
            $is_dir = false;
            if (!file_exists($file)) {
                $problemsFound = true;
                $this->results['file-' . $file] = [
                    "title" => "<i class='fa fa-file-o fa-fw'></i> File or directory does not exist.",
                    "message" => "We could not find the file or directory <code>$file</code>.",
                    "success" => false
                ];
            } else {
                $writeable = is_writable($file);
                if ($assertWriteable !== $writeable) {
                    $problemsFound = true;
                    $this->results['file-' . $file] = [
                        "title" => "<i class='fa fa-file-o fa-fw'></i> Incorrect permissions for file or directory.",
                        "message" => "<code>$file</code> is "
                            . ($writeable ? "writeable" : "not writeable")
                            . ", but it should "
                            . ($assertWriteable ? "be writeable" : "not be writeable")
                            . ".  Please modify the OS user or group permissions so that user <b>"
                            . exec('whoami') . "</b> "
                            . ($assertWriteable ? "has" : "does not have") . " write permissions for this directory.",
                        "success" => false
                    ];
                } else {
                    $this->results['file-' . $file] = [
                        "title" => "<i class='fa fa-file-o fa-fw'></i> File/directory check passed!",
                        "message" => "<code>$file</code> exists and is correctly set as <b>"
                            . ($writeable ? "writeable" : "not writeable")
                            . "</b>.",
                        "success" => true
                    ];  
                }
            }
        }
        return $problemsFound;     
    }
    
    /*
        // 3. Check database connection
        if (!Database::testConnection()){
            $messages[] = [
                "title" => "We couldn't connect to your database.",
                "message" => "Make sure that your database is properly configured in <code>config-userfrosting.php</code>, and that you have selected the correct configuration mode ('dev' or 'production').  Also, make sure that your database user has the proper privileges to connect to the database."
            ];
        }
        
        $tables = Database::getCreatedTables();
        if (count($tables) > 0){
            $messages[] = [
                "title" => "One or more tables already exist.",
                "message" => "The following tables already exist in the database: <strong>" . implode(", ", $tables) . "</strong>.  Do you already have another installation of UserFrosting in this database?  Please either create a new database (recommended), or change the table prefix in <code>config-userfrosting.php</code> if you cannot create a new database."
            ];
        }
        error_log("Done with checks");
        */

}