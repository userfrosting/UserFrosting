<?php
    require_once '../app/vendor/autoload.php';
    require_once 'utilities.php';

    use Carbon\Carbon;
    use Dotenv\Dotenv;
    use Dotenv\Exception\InvalidPathException;
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    use Slim\Container;
    use Slim\Http\Uri;
    use UserFrosting\Sprinkle\Account\Model\User;
    use UserFrosting\Sprinkle\Account\Util\Password;
    use UserFrosting\Sprinkle\Core\Initialize\SprinkleManager;

    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }

    // TODO: check PHP version


    // First, we create our DI container
    $container = new Container;

    // Attempt to fetch list of Sprinkles
    $sprinklesFile = file_get_contents('../app/sprinkles/sprinkles.json');
    if ($sprinklesFile === false) {
        die(PHP_EOL . "File 'app/sprinkles/sprinkles.json' not found. Please create a 'sprinkles.json' file and try again." . PHP_EOL);
    }
    $sprinkles = json_decode($sprinklesFile)->base;

    // Set up sprinkle manager service and list our Sprinkles.  Core sprinkle does not need to be explicitly listed.
    $container['sprinkleManager'] = function ($c) use ($sprinkles) {
        return new SprinkleManager($c, $sprinkles);
    };

    // Now, run the sprinkle manager to boot up all our sprinkles
    $container->sprinkleManager->init();

    $container->config['settings.displayErrorDetails'] = false;

    $config = $container->config;

    $container->db;

    // Test database connection
    try {
        Capsule::connection()->getPdo();
    } catch (\Exception $e) {
        $dbParams = $config['db.default'];
        die(PHP_EOL . "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration." . PHP_EOL);
    }

    $schema = Capsule::schema();

    $installTime = Carbon::now();

    $ufVersion = "4.0.0-alpha";

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

    // Get the installed versions
    echo PHP_EOL . "Checking for Sprinkle's version table:" . PHP_EOL;

    if (!$schema->hasTable('version')) {
        $schema->create('version', function (Blueprint $table) {
            $table->string('sprinkle', 45)->nullable();
            $table->string('version', 25)->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('sprinkle');
        });
        Capsule::table('version')->insert([
            [
                'sprinkle' => 'core',
                'version' => $ufVersion,
                'created_at' => $installTime,
                'updated_at' => $installTime
            ]
        ]);

        echo "Installing UserFrosting $ufVersion for the first time..." . PHP_EOL;
        echo "Created table 'version'..." . PHP_EOL;
    } else {
        echo "Table 'version' found." . PHP_EOL;
    }

    // Load the sprinkles list
    echo PHP_EOL . "Migrating Sprinkle's:" . PHP_EOL;

    // Looping throught every sprinkle and running their migration
    // N.B.: No migrations in core... yet. Add it manually if migration is added
    // to core at some point
    foreach ($sprinkles as $sprinkle) {

        echo ">> $sprinkle" . PHP_EOL;

        // Find all available version
        $migrations = glob("../app/sprinkles/$sprinkle/migrations/*.php");

        if (empty($migrations)) {
            echo "No migrations found for sprinkle '$sprinkle'..." . PHP_EOL.PHP_EOL;

        } else {

            // Get current installed version
            $installedVersion = Capsule::table('version')->where('sprinkle', $sprinkle)->first();
            $installedVersion = ($installedVersion != null) ? $installedVersion->version : 0;

            // Loop migrations files and run the ones we needs
            foreach ($migrations as $filepath) {
                $version = basename($filepath, ".php");
                if (version_compare($installedVersion, $version, "<")) {
                    require_once $filepath;
                }
            }

            if ($installedVersion == 0) {
                Capsule::table('version')->insert([
                    [
                        'sprinkle' => $sprinkle,
                        'version' => $version,
                        'created_at' => $installTime,
                        'updated_at' => $installTime
                    ]
                ]);

                echo "Migrated sprinkle '$sprinkle' to $version..." . PHP_EOL.PHP_EOL;

            } else if (version_compare($installedVersion, $version, "<")) {
                Capsule::table('version')->where('sprinkle', $sprinkle)
                    ->update(
                        [
                            'version' => $version,
                            'updated_at' => $installTime
                        ]
                    );

                echo PHP_EOL."Migrated sprinkle '$sprinkle' from $installedVersion to $version..." . PHP_EOL.PHP_EOL;
            } else {
                echo "Sprinkle '$sprinkle' already up-to-date..." . PHP_EOL.PHP_EOL;
            }
        }
    }

    // Make sure that there are no users currently in the user table
    // We setup the root account here so it can be done independent of the version check
    if (User::count() > 0) {

        echo PHP_EOL . "Table 'users' is not empty. Skipping root account setup. To set up the root account again, please truncate or drop the table and try again." . PHP_EOL;

    } else {

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
            "theme" => 'root',
            "password" => Password::hash($password)
        ]);

        $rootUser->save();
    }

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

    // Migrate the UF version
    Capsule::table('version')->where('sprinkle', 'core')
        ->update(
            [
                'version' => $ufVersion,
                'updated_at' => $installTime
            ]
        );

    echo PHP_EOL.PHP_EOL."UserFrosting migrated to $ufVersion successfully !".PHP_EOL;
