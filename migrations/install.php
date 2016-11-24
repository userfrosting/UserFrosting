<?php
    require_once '../app/vendor/autoload.php';    
    require_once 'utilities.php';

    use Carbon\Carbon;
    use Dotenv\Dotenv;
    use Dotenv\Exception\InvalidPathException;    
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Slim\Http\Uri;
    use UserFrosting\Sprinkle\Account\Model\User;
    use UserFrosting\Sprinkle\Account\Util\Password;
    
    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }
    
    // TODO: check PHP version
    
    // Grab any relevant dotenv variables from the .env file
    try {
        $dotenv = new Dotenv(\UserFrosting\APP_DIR);
        $dotenv->load();
    } catch (InvalidPathException $e) {
        // Skip loading the environment config file if it doesn't exist.
    }    
    
    // TODO: make this interactive?
    date_default_timezone_set('America/New_York');
    
    $capsule = new Capsule;

    // TODO: pull from config?
    $dbParams = [
        'driver'    => 'mysql',
        'host'      => getenv('DB_HOST'),
        'database'  => getenv('DB_NAME'),
        'username'  => getenv('DB_USER'),
        'password'  => getenv('DB_PASSWORD'),
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    ];

    $capsule->addConnection($dbParams);

    // Register as global connection
    $capsule->setAsGlobal();

    // Start Eloquent
    $capsule->bootEloquent();

    // Test database connection
    try {
        Capsule::connection()->getPdo();
    } catch (\Exception $e) {
        die(PHP_EOL . "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration." . PHP_EOL);
    }
    
    $schema = Capsule::schema();
    
    $installTime = Carbon::now();
    
    $detectedOS = php_uname('s');
    
    echo PHP_EOL . "Welcome to the UserFrosting installation tool!" . PHP_EOL;
    echo "The detected operating system is '$detectedOS'." . PHP_EOL;
    echo "Is this correct?  [Y/n]: ";
    
    $answer = trim(fgets(STDIN));
    
    if (!in_array(strtolower($answer), array('yes', 'y'))) {
        // OS
        echo PHP_EOL . "Please enter 'W' for a Windows-based operating system, or 'U' for OSX, Linux, or another Unix-based platform: ";
        $osCode = strtoupper(trim(fgets(STDIN)));
        while (!($osCode == 'W' || $osCode == 'U')) {
            echo 'Invalid selection, please try again: ';
            $osCode = strtoupper(trim(fgets(STDIN)));
        }
        
        if ($osCode == 'W') {
            $detectedOS = "Windows";
        } else {
            $detectedOS = "Unix";
        }
    }    
    
    echo PHP_EOL . "Creating initial tables:" . PHP_EOL;    
    
    // Sets up database and default groups, roles, and permissions
    require_once 'schema.php'; 

    // Make sure that there are no users currently in the user table
    if (User::count() > 0) {
        die(PHP_EOL . "Table 'users' must be empty to set up the root account.  Please truncate or drop the table and try again." . PHP_EOL);
    }
    
    echo PHP_EOL . 'To complete the installation process, you must set up a master (root) account.' . PHP_EOL;
    echo 'Please answer the following questions to complete this process:' . PHP_EOL;
    
    // Username
    echo PHP_EOL . 'Please choose a username (1-50 characters, no leading or trailing whitespace): ';
    $user_name = rtrim(fgets(STDIN), "\r\n");
    while (strlen($user_name) < 1 || strlen($user_name) > 50 || !filter_var($user_name, FILTER_VALIDATE_REGEXP, [
        'options' => [
            'regexp' => "/^\S((.*\S)|)$/"
        ]
    ])) {
        echo PHP_EOL . "Invalid username '$user_name', please try again: ";
        $user_name = rtrim(fgets(STDIN), "\r\n");
    }
    
    // Email
    echo PHP_EOL . 'Please choose a valid email address (1-254 characters, must be compatible with FILTER_VALIDATE_EMAIL): ';
    $email = rtrim(fgets(STDIN), "\r\n");
    while (strlen($email) < 1 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo PHP_EOL . "Invalid email '$email', please try again: ";
        $email = rtrim(fgets(STDIN), "\r\n");
    }
    
    // First name
    echo PHP_EOL . 'Please enter your first name (1-20 characters): ';
    $first_name = rtrim(fgets(STDIN), "\r\n");
    while (strlen($first_name) < 1 || strlen($first_name) > 20) {
        echo PHP_EOL . "Invalid first name '$first_name', please try again: ";
        $first_name = rtrim(fgets(STDIN), "\r\n");
    }
    
    // Last name
    echo PHP_EOL . 'Please enter your last name (1-30 characters): ';
    $last_name = rtrim(fgets(STDIN), "\r\n");
    while (strlen($last_name) < 1 || strlen($last_name) > 30) {
        echo PHP_EOL . "Invalid last name '$last_name', please try again: ";
        $last_name = rtrim(fgets(STDIN), "\r\n");
    }
    
    // Password
    echo PHP_EOL . 'Please choose a password (12-255 characters): ';
    $password = readPassword($detectedOS);
    while (strlen($password) < 12 || strlen($password) > 255) {
        echo PHP_EOL . 'Invalid password, please try again: ';
        $password = readPassword($detectedOS);
    }
       
    // Confirm password
    echo PHP_EOL . 'Please re-enter your chosen password: ';
    $password_confirm = readPassword($detectedOS);
    while ($password !== $password_confirm) {
        echo PHP_EOL . 'Passwords do not match, please try again. ';
        echo PHP_EOL . 'Please choose a password (12-255 characters): ';
        $password = readPassword($detectedOS);
        while (strlen($password) < 12 || strlen($password) > 255) {
            echo PHP_EOL . 'Invalid password, please try again: ';
            $password = readPassword($detectedOS);
        }
        echo PHP_EOL . 'Please re-enter your chosen password: ';
        $password_confirm = readPassword($detectedOS);
    }
    
    // Ok, now we've got the info and we can create the new user.
    
    $rootUser = new User([
        "user_name" => $user_name,
        "email" => $email,
        "first_name" => $first_name,
        "last_name" => $last_name,
        "password" => Password::hash($password)
    ]);
    
    $rootUser->save();
    
    /*
    $uri = new Uri(    
        empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
        trim($_SERVER['SERVER_NAME'], '/'),
        null,
        trim(realpath(__DIR__ . '/../public'), '/')
    );

    // Slim\Http\Uri likes to add trailing slashes when the path is empty, so this fixes that.
    $uri = trim($uri, '/');
    */
    
    echo PHP_EOL . "Installation is complete.  Please try signing in." . PHP_EOL;
    