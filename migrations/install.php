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
    use UserFrosting\Sprinkle\Core\Initialize\SprinkleManager;
    use UserFrosting\Sprinkle\Core\Model\Version;

    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }

    // 1° Pre-flight check and bootup
    // Check php version
    if (version_compare(phpversion(), UserFrosting\PHP_MIN_VERSION, "<")) {
        die('UserFrosting requires PHP version '.UserFrosting\PHP_MIN_VERSION.' or up.');
    }

    // First, we create our DI container
    $container = new Container;

    // Attempt to fetch list of Sprinkles
    $sprinklesFile = file_get_contents(UserFrosting\APP_DIR . '/' . UserFrosting\SPRINKLES_DIR_NAME . '/sprinkles.json');
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

    // Get config
    $config = $container->config;

    // Boot db
    $container->db;

    $dbParams = $config['db.default'];

    if (!$dbParams) {
        die(PHP_EOL . "'default' database connection not found.  Please double-check your configuration.");
    }

    // Test database connection directly using PDO
    try {
        $dsn = "{$dbParams['driver']}:host={$dbParams['host']};dbname={$dbParams['database']}";
        if (isset($dbParams['port'])) {
            $dsn .= ";port={$dbParams['port']}";
        }
        $dbh = new \PDO($dsn, $dbParams['username'], $dbParams['password']);
    } catch (\PDOException $e) {
        $message = PHP_EOL . "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration and/or google the exception shown below:" . PHP_EOL;
        $message .= "Exception: " . $e->getMessage() . PHP_EOL;
        $message .= "Trace: " . $e->getTraceAsString() . PHP_EOL;
        die($message);
    }

    $schema = Capsule::schema();

    // 2° Check Operating system
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

    // 3° Set-up version db table
    // Get the installed versions
    echo PHP_EOL . "Checking for Sprinkle's version table:" . PHP_EOL;

    if (!$schema->hasTable('version')) {
        $schema->create('version', function (Blueprint $table) {
            $table->string('sprinkle', 45);
            $table->string('version', 25);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('sprinkle');
        });

        echo "Created table 'version'..." . PHP_EOL;
    } else {
        echo "Table 'version' found." . PHP_EOL;
    }

    // 4° Migrate each sprinkles
    echo PHP_EOL . "Migrating Sprinkle's:" . PHP_EOL;

    // Add 'core'' to beginning sprinkles list for migration
    array_unshift($sprinkles, 'core');

    // Looping throught every sprinkle and running their migration
    foreach ($sprinkles as $sprinkle) {

        echo ">> $sprinkle" . PHP_EOL;

        // Find all available version
        $migrations = glob("../app/sprinkles/$sprinkle/migrations/*.php");

        if (empty($migrations)) {

            echo "No migrations found for sprinkle '$sprinkle'..." . PHP_EOL.PHP_EOL;

        } else {

            // Get sprinkle db version number
            $sprinkleVersion = Version::firstOrNew(['sprinkle' => $sprinkle]);

            // Loop migrations files and run the ones we needs
            foreach ($migrations as $filepath) {
                $migrationVersion = basename($filepath, ".php");
                if (version_compare($sprinkleVersion->version, $migrationVersion, "<")) {
                    require_once $filepath;
                    $sprinkleVersion->version = $migrationVersion;
                }
            }

            $sprinkleVersion->save();

            echo "Migrated sprinkle '$sprinkle' !" . PHP_EOL.PHP_EOL;
        }
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

    echo "UserFrosting migrated successfully !".PHP_EOL;
