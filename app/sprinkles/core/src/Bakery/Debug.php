<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\System\Bakery\DatabaseTest;

/**
 * Debug CLI tool.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Debug extends BaseCommand
{
    use DatabaseTest;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("debug")
             ->setDescription("Test the UserFrosting installation and setup the database")
             ->setHelp("This command is used to check if the various dependencies of UserFrosting are met and display useful debugging information. \nIf any error occurs, check out the online documentation for more info about that error. \nThis command also provide the necessary tools to setup the database credentials");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting");
        $this->io->writeln("UserFrosing version : " . \UserFrosting\VERSION);
        $this->io->writeln("OS Name : " . php_uname('s'));
        $this->io->writeln("Project Root : " . \UserFrosting\ROOT_DIR);

        // Need to touch the config service first to load dotenv values
        $config = $this->ci->config;
        $this->io->writeln("Environment mode : " . getenv("UF_MODE"));

        // Perform tasks
        $this->checkPhpVersion();
        $this->checkNodeVersion();
        $this->checkNpmVersion();
        $this->listSprinkles();
        $this->showConfig();
        $this->checkDatabase();

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success("Ready to bake !");
    }

    /**
     * Check the minimum version of php.
     * This is done by composer itself, but we do it again for good mesure
     *
     * @return void
     */
    protected function checkPhpVersion()
    {
        $this->io->writeln("PHP Version : " . phpversion());
        if (version_compare(phpversion(), \UserFrosting\PHP_MIN_VERSION, '<')) {
            $this->io->error("UserFrosting requires php version ".\UserFrosting\PHP_MIN_VERSION." or above. You'll need to update you PHP version before you can continue.");
            exit(1);
        }
    }

    /**
     * Check the minimum version requirement of Node installed
     *
     * @return void
     */
    protected function checkNodeVersion()
    {
        $npmVersion = trim(exec('node -v'));
        $this->io->writeln("Node Version : $npmVersion");

        if (version_compare($npmVersion, 'v4', '<')) {
            $this->io->error("UserFrosting requires Node version 4.x or above. Check the documentation for more details.");
            exit(1);
        }
    }

    /**
     * Check the minimum version requirement for Npm
     *
     * @return void
     */
    protected function checkNpmVersion()
    {
        $npmVersion = trim(exec('npm -v'));
        $this->io->writeln("NPM Version : $npmVersion");

        if (version_compare($npmVersion, '3', '<')) {
            $this->io->error("UserFrosting requires npm version 3.x or above. Check the documentation for more details.");
            exit(1);
        }
    }

    /**
     * List all sprinkles defined in the Sprinkles schema file,
     * making sure this file exist at the same time
     *
     * @return void
     */
    protected function listSprinkles()
    {
        // Check for Sprinkles schema file
        $path = \UserFrosting\SPRINKLES_SCHEMA_FILE;
        $sprinklesFile = @file_get_contents($path);
        if ($sprinklesFile === false) {
            $this->io->error("The file `$path` not found.");
        }

        // List installed sprinkles
        $sprinkles = json_decode($sprinklesFile)->base;
        $this->io->section("Loaded sprinkles");
        $this->io->listing($sprinkles);

        // Throw fatal error if the `core` sprinkle is missing
        if (!in_array("core", $sprinkles)) {
            $this->io->error("The `core` sprinkle is missing from the 'sprinkles.json' file.");
            exit(1);
        }
    }

    /**
     * Check the database connexion and setup the `.env` file if we can't
     * connect and there's no one found.
     *
     * @return void
     */
    protected function checkDatabase()
    {
        $this->io->section("Testing database connection...");

        try {
            $this->testDB();
            $this->io->writeln("Database connection successful");
            return;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->io->error($error);
            exit(1);
        }
    }

    /**
     * Display database config as for debug purposes
     *
     * @return void
     */
    protected function showConfig()
    {
        // Get config
        $config = $this->ci->config;

        // Display database info
        $this->io->section("Database config");
        $this->io->writeln([
            "DRIVER : " . $config['db.default.driver'],
            "HOST : " . $config['db.default.host'],
            "PORT : " . $config['db.default.port'],
            "DATABASE : " . $config['db.default.database'],
            "USERNAME : " . $config['db.default.username'],
            "PASSWORD : " . ($config['db.default.password'] ? "*********" : "")
        ]);
    }
}
