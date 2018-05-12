<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Command;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Support\Repository\Repository as Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * DB Setup Wizard CLI Tools.
 * Helper command to setup database config in .env file
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupDbCommand extends BaseCommand
{
    /**
     * @var string Path to the .env file
     */
    protected $envPath = \UserFrosting\APP_DIR. '/.env';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("setup:db")
             ->setDescription("UserFrosting Database Configuration Wizard")
             ->setHelp("Helper command to setup the database configuration. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.")
             ->addOption('db_driver', null, InputOption::VALUE_OPTIONAL, "The database driver {$this->getDatabaseDriversList()}")
             ->addOption('db_name', null, InputOption::VALUE_OPTIONAL, "The database name")
             ->addOption('db_host', null, InputOption::VALUE_OPTIONAL, "The database hostname")
             ->addOption('db_port', null, InputOption::VALUE_OPTIONAL, "The database port")
             ->addOption('db_user', null, InputOption::VALUE_OPTIONAL, "The database user")
             ->addOption('db_password', null, InputOption::VALUE_OPTIONAL, "The database password");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Config $config Get config
         */
        $config = $this->ci->config;

        // Display header,
        $this->io->title("UserFrosting's Database Setup Wizard");
        $this->io->note("Database credentials will be saved in `{$this->envPath}`");

        // Get an instance of the DotenvEditor
        $dotenvEditor = new DotenvEditor(\UserFrosting\APP_DIR, false);
        $dotenvEditor->load($this->envPath);
        $dotenvEditor->save(); // Save make sure empty file is created if none exist before reading it

        // Get keys
        $keys = [
            'DB_HOST' => ($dotenvEditor->keyExists('DB_HOST')) ? $dotenvEditor->getValue('DB_HOST') : '',
            'DB_NAME' => ($dotenvEditor->keyExists('DB_NAME')) ? $dotenvEditor->getValue('DB_NAME') : '',
            'DB_USER' => ($dotenvEditor->keyExists('DB_USER')) ? $dotenvEditor->getValue('DB_USER') : '',
            'DB_PASSWORD' => ($dotenvEditor->keyExists('DB_PASSWORD')) ? $dotenvEditor->getValue('DB_PASSWORD') : ''
        ];

        // There may be some custom config or global env values defined on the server.
        // We'll check for that and ask for confirmation in this case.
        if ($config["db.default.host"] != $keys['DB_HOST'] ||
            $config["db.default.database"] != $keys['DB_NAME'] ||
            $config["db.default.username"] != $keys['DB_USER'] ||
            $config["db.default.password"] != $keys['DB_PASSWORD']) {

            $this->io->warning("Current database configuration differ from the configuration defined in `{$this->envPath}`. Global system environment variables might be defined.");

            if (!$this->io->confirm('Continue?', false)) {
                return;
            }
        }

        // Get database info
        $dbParams = $this->askForDatabase($input);

        // Test database
        $this->testDatabase($dbParams);

        // Time to save
        $this->io->section("Saving data");

        // Prepare file content
        // N.B.: Can't use the `$dbParams` keys directly since they differ from
        // the config one later used to update the config
        $fileContent = [
            "DB_DRIVER" => $dbParams['driver'],
            "DB_HOST" => $dbParams['host'],
            "DB_PORT" => $dbParams['port'],
            "DB_NAME" => $dbParams['database'],
            "DB_USER" => $dbParams['username'],
            "DB_PASSWORD" => $dbParams['password']
        ];

        foreach ($fileContent as $key => $value) {
            $dotenvEditor->setKey($key, $value);
        }
        $dotenvEditor->save();

        // At this point, `$this->uf` is still using the old configs.
        // We need to refresh the `db.default` config values
        $newConfig = array_merge($this->ci->config['db.default'], $dbParams);
        $this->ci->config->set("db.default", $newConfig);

        // Success
        $this->io->success("Database config successfully saved in `{$this->envPath}`");
    }

    /**
     * Ask for database crendentials
     *
     * @param  InputInterface $args Command arguments
     * @return array The databse credentials
     */
    protected function askForDatabase(InputInterface $args)
    {
        // Get the db driver choices
        $drivers = $this->databaseDrivers();
        $driversList = $drivers->pluck('name')->toArray();

        // Ask for database type if not defined in command arguments
        if ($args->getOption('db_driver')) {
            $selectedDriver = $args->getOption('db_driver');
            $driver = $drivers->where('driver', $selectedDriver)->first();
        } else {
            $selectedDriver = $this->io->choice("Database type", $driversList);
            $driver = $drivers->where('name', $selectedDriver)->first();
        }

        // Get the selected driver. Make sure driver was found
        if (!$driver) {
            $this->io->error("Invalid database driver: $selectedDriver");
            exit(1);
        }

        // Ask further questions based on driver
        if ($driver['driver'] == 'sqlite') {

            $name = ($args->getOption('db_name')) ?: $this->io->ask("Database name", $driver['defaultDBName']);

            return [
                'driver' => $driver['driver'],
                'host' => '',
                'port' => '',
                'database' => $name,
                'username' => '',
                'password' => ''
            ];

        } else {
            $defaultPort = $driver['defaultPort'];

            $host = ($args->getOption('db_host')) ?: $this->io->ask("Hostname", "localhost");
            $port = ($args->getOption('db_port')) ?: $this->io->ask("Port", $defaultPort);
            $name = ($args->getOption('db_name')) ?: $this->io->ask("Database name", $driver['defaultDBName']);
            $user = ($args->getOption('db_user')) ?: $this->io->ask("Username", "userfrosting");
            $password = ($args->getOption('db_password')) ?: $this->io->askHidden("Password", function ($password) {
                // Use custom validator to accept empty password
                return $password;
            });

            return [
                'driver' => $driver['driver'],
                'host' => $host,
                'port' => $port,
                'database' => $name,
                'username' => $user,
                'password' => $password,
                'charset' => $this->ci->config['db.default.charset'] // Used when replacing config later
            ];
        }
    }

    /**
     * Test new database connecion
     *
     * @param  array $dbParams Database params
     * @return void (Exit if fails)
     */
    protected function testDatabase($dbParams)
    {
        // Setup a new db connection
        $capsule = new Capsule;
        $capsule->addConnection($dbParams);

        // Test the db connexion.
        try {
            $conn = $capsule->getConnection();
            $conn->getPdo();
            $this->io->success("Database connection successful");
        } catch (\PDOException $e) {
            $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}':".PHP_EOL;
            $message .= "Exception: " . $e->getMessage() . PHP_EOL.PHP_EOL;
            $message .= "Please check your database configuration and/or google the exception shown above and run the command again.";
            $this->io->error($message);
            exit(1);
        }
    }

    /**
     * Return the database choices for the env setup.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function databaseDrivers()
    {
        return collect([
            [
                "driver" => "mysql",
                "name" => "MySQL / MariaDB",
                "defaultDBName" => "userfrosting",
                "defaultPort" => 3306
            ],
            [
                "driver" => "pgsql",
                "name" => "ProgreSQL",
                "defaultDBName" => "userfrosting",
                "defaultPort" => 5432
            ],
            [
                "driver" => "sqlsrv",
                "name" => "SQL Server",
                "defaultDBName" => "userfrosting",
                "defaultPort" => 1433
            ],
            [
                "driver" => "sqlite",
                "name" => "SQLite",
                "defaultDBName" => \UserFrosting\DB_DIR . \UserFrosting\DS . 'userfrosting.db',
                "defaultPort" => null
            ]
        ]);
    }

    /**
     * Returns a list of available drivers
     *
     * @return array
     */
    protected function getDatabaseDriversList()
    {
        $dbDriverList = $this->databaseDrivers();
        $dbDriverList = $dbDriverList->pluck('driver');
        return $dbDriverList;
    }
}
