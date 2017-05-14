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
use UserFrosting\System\Bakery\EnvSetup;
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
        $this->checkEnv();

        // Before going further, will try to load the UF Container
        $this->getContainer();

        // Now that we have the container, we can test it and try to get the configs values
        // And test the db
        $this->showConfig();
        $this->testDB(true);

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
     * Check if `app/.env` exist. Throw warning if not
     *
     * @access public
     * @return void
     */
    protected function checkEnv()
    {
        // Check if the .env file is define. If it it, we'll go directly to testing the database.
        $path = \UserFrosting\APP_DIR. '/.env';
        if (!file_exists($path)) {

            // File wasn't found. Not fatal yet, just show a warning for now
            $this->io->warning("\nFile `$path` not found. ");
            $this->io->write("This file is used to define your database credentials and other environment variables.\nNote: You might have global environment values defined on this machine instead.");

            // Ask if we should setup .env
            $setupEnv = $this->io->askConfirmation("Do you want to setup a `.env` file now? [y/N] ", false);

            if ($setupEnv) {

                $envSetup = new EnvSetup($this->io, $this->composer);
                $envSetup->setupEnv();
                $this->io->write("\n<comment>If you can't connect to the database, edit the parameters in your newly created `.env` file or run `composer run-script setup-env`.</>");
            }
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
            $this->io->error("\nFATAL ERROR :: File `$path` not found. Please create a 'sprinkles.json' file and try again.");
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
            $this->io->error("\nFATAL ERROR :: The `core` sprinkle is missing from the 'sprinkles.json' file.");
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
}