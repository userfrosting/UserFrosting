<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Composer\Script\Event;
use UserFrosting\System\Bakery\Bakery;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

/**
 * Debug CLI Tools.
 * Perform the preflight check for UserFrosting install
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Debug extends Bakery
{
    use Traits\DatabaseTest;

    /**
     * Run the `debug` composer script
     *
     * @access public
     * @static
     * @param Event $event
     * @return void
     */
    public static function main(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());
        $bakery->run();
    }

    /**
     * Run the debug script.
     *
     * @access public
     * @return void
     */
    public function run()
    {
        // Display header,
        $this->io->write("<info>{$this->title()}</info>");
        $this->io->write("UserFrosing version : " . \UserFrosting\VERSION);
        $this->io->write("OS Name : " . php_uname('s'));
        $this->io->write("Project Root : {$this->projectRoot}");

        // Perform tasks
        $this->checkPhpVersion();
        $this->checkNodeVersion();
        $this->checkNpmVersion();
        $this->listSprinkles();

        // Before going further, will try to load the UF Container
        $this->getContainer();

        // Go to the env setup
        $this->checkDatabase();

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->write("\n<fg=black;bg=green>Ready to bake !</>\n");
    }

    /**
     * Check the minimum version of php.
     * This is done by composer itself, but we do it again for good mesure
     *
     * @access public
     * @return void
     */
    protected function checkPhpVersion()
    {
        $this->io->write("PHP Version : " . phpversion());
        if (version_compare(phpversion(), \UserFrosting\PHP_MIN_VERSION, '<')) {
            $this->io->error("\nFATAL ERROR :: UserFrosting requires php version ".\UserFrosting\PHP_MIN_VERSION." or above. You'll need to update you PHP version before you can continue.");
            exit(1);
        }
    }

    /**
     * Check the minimum version requirement of Node installed
     *
     * @access public
     * @return void
     */
    protected function checkNodeVersion()
    {
        $npmVersion = trim(exec('node -v'));
        $this->io->write("Node Version : $npmVersion");

        if (version_compare($npmVersion, 'v4', '<')) {
            $this->io->error("\nFATAL ERROR :: UserFrosting requires Node version 4.x or above. Check the documentation for more details.");
            exit(1);
        }
    }

    /**
     * Check the minimum version requirement for Npm
     *
     * @access public
     * @return void
     */
    protected function checkNpmVersion()
    {
        $npmVersion = trim(exec('npm -v'));
        $this->io->write("NPM Version : $npmVersion");

        if (version_compare($npmVersion, '3', '<')) {
            $this->io->error("\nFATAL ERROR :: UserFrosting requires npm version 3.x or above. Check the documentation for more details.");
            exit(1);
        }
    }

    /**
     * List all sprinkles defined in the `sprinkles.json` file,
     * making sure this file exist at the same time
     *
     * @access protected
     * @return void
     */
    protected function listSprinkles()
    {
        // Check for `sprinkles.json`
        $path = \UserFrosting\APP_DIR . '/sprinkles.json';
        $sprinklesFile = @file_get_contents($path);
        if ($sprinklesFile === false) {
            $sprinklesFile = $this->setupBaseSprinkleList();
        }

        // List installed sprinkles
        $sprinkles = json_decode($sprinklesFile)->base;
        $this->io->write("\n<info>Loaded sprinkles :</info>");
        foreach ($sprinkles as $sprinkle) {
            $this->io->write("  - ".$sprinkle);
        }

        // Throw fatal error if the `core` sprinkle is missing
        if (!in_array("core", $sprinkles)) {
            $this->io->error("\nFATAL ERROR :: The `core` sprinkle is missing from the 'sprinkles.json' file.");
            exit(1);
        }
    }

    protected function setupBaseSprinkleList()
    {
        $model = \UserFrosting\APP_DIR . '/sprinkles.example.json';
        $destination = \UserFrosting\APP_DIR . '/sprinkles.json';
        $sprinklesModelFile = @file_get_contents($model);
        if ($sprinklesModelFile === false) {
            $this->io->error("\nFATAL ERROR :: File `$sprinklesModelFile` not found. Please create '$destination' manually and try again.");
            exit(1);
        }

        file_put_contents($destination, $sprinklesModelFile);

        return $sprinklesModelFile;
    }

    /**
     * Check the database connexion and setup the `.env` file if we can't
     * connect and there's no one found.
     *
     * @access protected
     * @return void
     */
    protected function checkDatabase()
    {
        // First thing, silently test database. If it works, our job is done here
        try {
            $this->testDB();
            $this->showConfig();
            $this->io->write("\n<info>Testing database connexion...</info>");
            $this->io->write("Database connexion successful");
            return;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $config = $this->ci->config;

        // Check if the .env file exist. At this point, we can't connect and
        // there's no .env, we're gonna have a bad time
        $path = \UserFrosting\APP_DIR. '/.env';
        if (!file_exists($path)) {

            // If  the configs are empty, we'll assume nothing is defined and go strait to setup.
            // Otherwise, we'll ask first. There may be some custom config or global env values defined here that are not right
            if ($config["db.default.host"] == "" || $config["db.default.database"] == "" || $config["db.default.username"] == "") {
                $setupEnv = true;
            } else {
                $this->io->warning("\nFile `$path` not found. ");
                $this->io->write("This file is used to define your database credentials and other environment variables. You may also have another means of (direct config values or global environment vars).");
                $setupEnv = $this->io->askConfirmation("Do you want to setup a `.env` file now? [y/N] ", false);
            }

            if ($setupEnv) {
                $this->setupEnv();
                return;
            }
        }

        // We have an error message. We'll display the current config then the error message
        $this->showConfig();
        $this->io->write("\n<info>Testing database connexion...</info>");
        $this->io->error($e->getMessage());
        exit(1);
    }

    /**
     * Setup the `.env` file.
     *
     * @access public
     * @return void
     */
    public function setupEnv()
    {
        // Get config
        $config = $this->ci->config;

        $success = false;

        while (!$success) {

            // Ask the questions
            $this->io->write("\n<info>Enter your database credentials :</info>");
            $host = $this->io->ask("Hostname [localhost]: ", "localhost");
            $port = $this->io->ask("Port [3306]: ", "3306");
            $name = $this->io->ask("Database name [userfrosting]: ", "userfrosting");
            $user = $this->io->ask("Username [userfrosting]: ", "userfrosting");
            $password = $this->io->askAndHideAnswer("Password: ");

            // Setup a new db connection
            $capsule = new Capsule;
            $dbParams = [
                'driver' => "mysql",
                'host' => $host,
                'port' => $port,
                'database' => $name,
                'username' => $user,
                'password' => $password
            ];
            $capsule->addConnection($dbParams);

            // Test the db connexion.
            try {
                $conn = $capsule->getConnection();
                $conn->getPdo();
                $this->io->write("Database connexion successful");
                $success = true;
            } catch (\PDOException $e) {
                $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration and/or google the exception shown below:".PHP_EOL;
                $message .= "Exception: " . $e->getMessage() . PHP_EOL;
                $this->io->error($message);
            }
        }

        // Ask for the smtp values now
        //!TODO

        $fileContent = [
            "UF_MODE=\"\"\n",
            "DB_DRIVER=\"{$dbParams['driver']}\"\n",
            "DB_HOST=\"{$dbParams['host']}\"\n",
            "DB_PORT=\"{$dbParams['port']}\"\n",
            "DB_NAME=\"{$dbParams['database']}\"\n",
            "DB_USER=\"{$dbParams['username']}\"\n",
            "DB_PASSWORD=\"{$dbParams['password']}\"\n",
            "SMTP_HOST=\"host.example.com\"\n",
            "SMTP_USER=\"relay@example.com\"\n",
            "SMTP_PASSWORD=\"password\"\n"
        ];

        // Let's save this config
        file_put_contents(\UserFrosting\APP_DIR. '/.env', $fileContent);
    }

    /**
     * Display database config as for debug purposes
     *
     * @access protected
     * @return void
     */
    protected function showConfig()
    {
        // Get config
        $config = $this->ci->config;

        // Display database info
        $this->io->write("\n<info>Database config :</info>");
        $this->io->write(" DRIVER : " . $config['db.default.driver']);
        $this->io->write(" HOST : " . $config['db.default.host']);
        $this->io->write(" PORT : " . $config['db.default.port']);
        $this->io->write(" DATABASE : " . $config['db.default.database']);
        $this->io->write(" USERNAME : " . $config['db.default.username']);
        $this->io->write(" PASSWORD : " . ($config['db.default.password'] ? "*********" : ""));
    }
}