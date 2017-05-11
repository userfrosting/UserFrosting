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
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Debug CLI Tools.
 * Perform the preflight check for UserFrosting install
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Debug extends Bakery
{
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
        $this->io->write("\n<info>/*************************/\n/* UserFrosting's Bakery */\n/*************************/</info>");
        $this->io->write("UserFrosing version : " . \UserFrosting\VERSION);
        $this->io->write("OS Name : " . php_uname('s'));
        $this->io->write("Project Root : {$this->projectRoot}");

        // Perform tasks
        $this->checkPhpVersion();
        $this->checkNodeVersion();
        $this->checkNpmVersion();
        $this->checkEnv();
        $this->listSprinkles();

        // Before goin further, will try to load the UF Container
        $this->getContainer();

        // Now that we have the container, we can test it and try to get the configs values
        // And test the db
        $this->showConfig();
        $this->testDB();

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
            $this->io->write("\n<error>FATAL ERROR :: UserFrosting requires php version ".\UserFrosting\PHP_MIN_VERSION." or above. You'll need to update you PHP version before you can continue</error>");
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
            $this->io->writeError("\n<error>FATAL ERROR :: UserFrosting requires Node version 4.x or above. Check the documentation for more details.</error>");
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
            $this->io->write("\n<error>FATAL ERROR :: UserFrosting requires npm version 3.x or above. Check the documentation for more details.</error>");
            exit(1);
        }
    }

    /**
     * Check if `app/.env` exist. Throw warning if not
     *
     * @access public
     * @return void
     */
    protected function checkEnv()
    {
        $path = \UserFrosting\APP_DIR. '/.env';
        if (!file_exists($path)) {
            $this->io->write("\n<warning>File `$path` not found. This file is used to define your database credentials, but you might have global environment values set on your machine. Make sure the database config below are right.</warning>");
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
            $this->io->write("\n<error>FATAL ERROR :: File `$path` not found. Please create a 'sprinkles.json' file and try again.</error>");
            exit(1);
        }

        // List installed sprinkles
        $sprinkles = json_decode($sprinklesFile)->base;
        $this->io->write("\n<info>Loaded sprinkles :</info>");
        foreach ($sprinkles as $sprinkle) {
            $this->io->write("  - ".$sprinkle);
        }

        // Throw fatal error if the `core` sprinkle is missing
        if (!in_array("core", $sprinkles)) {
            $this->io->write("\n<error>FATAL ERROR :: The `core` sprinkle is missing from the 'sprinkles.json' file.</error>");
            exit(1);
        }
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

    protected function testDB()
    {
        $this->io->write("\n<info>Testing database connexion...</info>");

        // Boot db
        $this->ci->db;

        // Get config
        $config = $this->ci->config;

        // Check params are valids
        $dbParams = $config['db.default'];

        if (!$dbParams) {
            $this->io->write("\n<error>'default' database connection not found.  Please double-check your configuration.</error>");
            exit(1);
        }

        // Test database connection directly using PDO
        try {
            Capsule::connection()->getPdo();
        } catch (\PDOException $e) {

            $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration and/or google the exception shown below:".PHP_EOL;
            $message .= "Exception: " . $e->getMessage() . PHP_EOL;
            $message .= "Trace: " . $e->getTraceAsString() . PHP_EOL;

            $this->io->write("\n<error>$message</error>");
            exit(1);
        }

        $this->io->write("Database connexion successful");
    }
}