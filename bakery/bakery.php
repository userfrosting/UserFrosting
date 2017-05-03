<?php

    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }

    // Require composer autoload file. Not having this file means Composer might not be installed / run
    if (!file_exists(__DIR__.'/../app/vendor/autoload.php')) {
        echo PHP_EOL.PHP_EOL."ERROR :: File `app/vendor/autoload.php` not found. This indicate that composer has not yet been run on this install. Install composer and run `composer install` from the `app/` directory. Check the documentation for more details.".PHP_EOL;
        exit(1);
    } else {
        require_once __DIR__.'/../app/vendor/autoload.php';
    }

    use Slim\Container;
    use UserFrosting\Sprinkle\Core\Initialize\SprinkleManager;

    // Output current UF version
    echo "\n/*************************/\n/* UserFrosting's Bakery */\n/*************************/";
    echo PHP_EOL." UserFrosing version : " . UserFrosting\VERSION;
    echo PHP_EOL." OS Name : " . php_uname('s');

    // Check php version . Should already be done by Composer
    echo PHP_EOL." PHP Version : " . phpversion();
    if (version_compare(phpversion(), UserFrosting\PHP_MIN_VERSION, '<')) {
        echo PHP_EOL.PHP_EOL."ERROR :: UserFrosting requires php version ".UserFrosting\PHP_MIN_VERSION." or above. You'll need to update you PHP version before you can continue".PHP_EOL;
        exit(1);
    }

    // Check npm version
    $npmVersion = trim(exec('npm -v'));
    echo PHP_EOL." NPM Version : " . $npmVersion;
    if (version_compare($npmVersion, '3', '<')) {
        echo PHP_EOL.PHP_EOL."ERROR :: UserFrosting requires npm version 3.x or above. Check the documentation for more details.".PHP_EOL;
        exit(1);
    }

    // Check `.env`
    if (!file_exists(__DIR__.'/../app/.env')) {
        echo PHP_EOL.PHP_EOL." WARNING :: File `app/.env` not found. This file is used to define your database credentials, but you might have global environment values set on your machine. Make sure the database config below are right.".PHP_EOL;
    }

    // Check for `sprinkles.json`
    $sprinklesFile = @file_get_contents(UserFrosting\APP_DIR . '/' . UserFrosting\SPRINKLES_DIR_NAME . '/sprinkles.json');
    if ($sprinklesFile === false) {
        echo PHP_EOL.PHP_EOL."ERROR :: File 'app/sprinkles/sprinkles.json' not found. Please create a 'sprinkles.json' file and try again.".PHP_EOL;
        exit(1);
    }

    // List installed sprinkles
    $sprinkles = json_decode($sprinklesFile)->base;
    echo PHP_EOL." Loaded sprinkles :".PHP_EOL;
    foreach ($sprinkles as $sprinkle) {
        echo "  - ".$sprinkle.PHP_EOL;
    }

    // First, we create our DI container
    $container = new Container;

    // Set up sprinkle manager service and list our Sprinkles.  Core sprinkle does not need to be explicitly listed.
    $container['sprinkleManager'] = function ($c) use ($sprinkles) {
        return new SprinkleManager($c, $sprinkles);
    };

    // Now, run the sprinkle manager to boot up all our sprinkles
    $container->sprinkleManager->init();

    $container->config['settings.displayErrorDetails'] = false;

    // Get config
    $config = $container->config;

    // Display database info
    echo PHP_EOL." Database config : ";
    echo PHP_EOL."    DRIVER : " . $config['db.default.driver'];
    echo PHP_EOL."    HOST : " . $config['db.default.host'];
    echo PHP_EOL."    PORT : " . $config['db.default.port'];
    echo PHP_EOL."    DATABASE : " . $config['db.default.database'];
    echo PHP_EOL."    USERNAME : " . $config['db.default.username'];
    echo PHP_EOL."    PASSWORD : " . ($config['db.default.password'] ? "*********" : "");
    echo PHP_EOL;

    // Done with the bakery
    //echo PHP_EOL."Ready to bake!";
    echo PHP_EOL.PHP_EOL;