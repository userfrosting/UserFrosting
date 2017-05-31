<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\Bakery;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Debug CLI Tools.
 * Perform the preflight check for UserFrosting install
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class DebugCommand extends Bakery
{
    use Traits\DatabaseTest;

    /**
     * @var String $ufArt The UserFrosting ASCII art.
     */
    public $title = "
 _   _              ______             _   _
| | | |             |  ___|           | | (_)
| | | |___  ___ _ __| |_ _ __ ___  ___| |_ _ _ __   __ _
| | | / __|/ _ \ '__|  _| '__/ _ \/ __| __| | '_ \ / _` |
| |_| \__ \  __/ |  | | | | | (_) \__ \ |_| | | | | (_| |
 \___/|___/\___|_|  \_| |_|  \___/|___/\__|_|_| |_|\__, |
                                                    __/ |
                                                   |___/";

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
        $this->io->writeln("<info>{$this->title}</info>");
        $this->io->writeln("UserFrosing version : " . \UserFrosting\VERSION);
        $this->io->writeln("OS Name : " . php_uname('s'));
        $this->io->writeln("Project Root : {$this->projectRoot}");

        // Perform tasks
        $this->checkPhpVersion();
        $this->checkNodeVersion();
        $this->checkNpmVersion();
        $this->listSprinkles();

        // Go to the env setup
        $this->checkDatabase();

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success("Ready to bake !");
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
        $this->io->writeln("PHP Version : " . phpversion());
        if (version_compare(phpversion(), \UserFrosting\PHP_MIN_VERSION, '<')) {
            $this->io->error("UserFrosting requires php version ".\UserFrosting\PHP_MIN_VERSION." or above. You'll need to update you PHP version before you can continue.");
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
        $this->io->writeln("Node Version : $npmVersion");

        if (version_compare($npmVersion, 'v4', '<')) {
            $this->io->error("UserFrosting requires Node version 4.x or above. Check the documentation for more details.");
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
        $this->io->writeln("NPM Version : $npmVersion");

        if (version_compare($npmVersion, '3', '<')) {
            $this->io->error("UserFrosting requires npm version 3.x or above. Check the documentation for more details.");
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
        $this->io->section("Loaded sprinkles");
        $this->io->listing($sprinkles);

        // Throw fatal error if the `core` sprinkle is missing
        if (!in_array("core", $sprinkles)) {
            $this->io->error("The `core` sprinkle is missing from the 'sprinkles.json' file.");
            exit(1);
        }
    }

    /**
     * Write the base `sprinkle.json` file if none exist.
     *
     * @access protected
     * @return void
     */
    protected function setupBaseSprinkleList()
    {
        $model = \UserFrosting\APP_DIR . '/sprinkles.example.json';
        $destination = \UserFrosting\APP_DIR . '/sprinkles.json';
        $sprinklesModelFile = @file_get_contents($model);
        if ($sprinklesModelFile === false) {
            $this->io->error("File `$sprinklesModelFile` not found. Please create '$destination' manually and try again.");
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
            $this->io->section("Testing database connexion...");
            $this->io->writeln("Database connexion successful");
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
                $this->io->writeln("This file is used to define your database credentials and other environment variables. You may also have another means of (direct config values or global environment vars).");
                $setupEnv = $this->io->confirm("Do you want to setup a `.env` file now? [y/N] ", false);
            }

            if ($setupEnv) {
                $this->setupEnv();
                return;
            }
        }

        // We have an error message. We'll display the current config then the error message
        $this->showConfig();
        $this->io->section("Testing database connexion...");
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

        // Get the db driver choices
        $drivers = $this->databaseDrivers();

        while (!$success) {

            // Ask the questions
            $this->io->section("Setting up database");
            $this->io->note("Database credentials will be saved in `app/.env`");

            $driver = $this->io->choice("Database type", $drivers->pluck('name')->toArray());
            $driver = $drivers->where('name', $driver)->first();

            $driverName = $driver['driver'];
            $defaultPort = $driver['defaultPort'];

            $host = $this->io->ask("Hostname", "localhost");
            $port = $this->io->ask("Port", $defaultPort);
            $name = $this->io->ask("Database name", "userfrosting");
            $user = $this->io->ask("Username", "userfrosting");
            $password = $this->io->askHidden("Password: ");

            // Setup a new db connection
            $capsule = new Capsule;
            $dbParams = [
                'driver' => $driverName,
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
                $this->io->success("Database connexion successful");
                $success = true;
            } catch (\PDOException $e) {
                $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration and/or google the exception shown below:".PHP_EOL;
                $message .= "Exception: " . $e->getMessage() . PHP_EOL;
                $this->io->error($message);
            }
        }

        // Ask for the smtp values now
        $this->io->section("Enter your SMTP credentials");
        $this->io->write("This is use to send emails from the system. Edit `app/.env` if you have problem with this later.");
        $smtpHost = $this->io->ask("SMTP Host", "");
        $smtpUser = $this->io->ask("SMTP User", "");
        $smtpPassword = $this->io->askHidden("SMTP Password");


        $fileContent = [
            "UF_MODE=\"\"\n",
            "DB_DRIVER=\"{$dbParams['driver']}\"\n",
            "DB_HOST=\"{$dbParams['host']}\"\n",
            "DB_PORT=\"{$dbParams['port']}\"\n",
            "DB_NAME=\"{$dbParams['database']}\"\n",
            "DB_USER=\"{$dbParams['username']}\"\n",
            "DB_PASSWORD=\"{$dbParams['password']}\"\n",
            "SMTP_HOST=\"$smtpHost\"\n",
            "SMTP_USER=\"$smtpUser\"\n",
            "SMTP_PASSWORD=\"$smtpPassword\"\n"
        ];

        // Let's save this config
        file_put_contents(\UserFrosting\APP_DIR. '/.env', $fileContent);
    }

    /**
     * Return the database choices for the env setup.
     *
     * @access protected
     * @return void
     */
    protected function databaseDrivers()
    {
        return collect([
            [
                "driver" => "mysql",
                "name" => "MySQL / MariaDB",
                "defaultPort" => 3306
            ],
            [
                "driver" => "pgsql",
                "name" => "ProgreSQL",
                "defaultPort" => 5432
            ],
            [
                "driver" => "sqlsrv",
                "name" => "SQL Server",
                "defaultPort" => 1433
            ]
        ]);
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
        $this->io->section("Database config :");
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