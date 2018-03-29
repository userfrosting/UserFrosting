<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Setup wizard CLI Tools.
 * Helper command to setup .env file
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupCommand extends BaseCommand
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
        $this->setName("setup")
             ->setDescription("UserFrosting configuration wizard")
             ->setHelp("Helper command to setup the database and email configuration. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.")
             ->addOption("force", "f", InputOption::VALUE_NONE, "If `.env` file exist, force setup to run again")
             ->addOption('db_driver', null, InputOption::VALUE_OPTIONAL, "The database driver {$this->getDatabaseDriversList()}")
             ->addOption('db_name', null, InputOption::VALUE_OPTIONAL, "The database name")
             ->addOption('db_host', null, InputOption::VALUE_OPTIONAL, "The database hostname")
             ->addOption('db_port', null, InputOption::VALUE_OPTIONAL, "The database port")
             ->addOption('db_user', null, InputOption::VALUE_OPTIONAL, "The database user")
             ->addOption('db_password', null, InputOption::VALUE_OPTIONAL, "The database password")
             ->addOption('smtp_host', null, InputOption::VALUE_OPTIONAL, "The SMTP server hostname")
             ->addOption('smtp_user', null, InputOption::VALUE_OPTIONAL, "The SMTP server user")
             ->addOption('smtp_password', null, InputOption::VALUE_OPTIONAL, "The SMTP server password");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get config
        $config = $this->ci->config;

        // Get options
        $force = $input->getOption('force');

        // Display header,
        $this->io->title("UserFrosting's Setup Wizard");

        // Check if the .env file exist.
        if (!$force && file_exists($this->envPath)) {
            $this->io->note("File `{$this->envPath}` already exist. Use the `php bakery setup -f` command to force setup to run again.");
            return;
        }

        // There might not be any `.env` file because there may be some custom config or global env values defined on the server.
        // We'll check for that. If the configs are empty, we'll assume nothing is defined and go strait to setup.
        if (!$force && $config["db.default.host"] != "" && $config["db.default.database"] != "" && $config["db.default.username"] != "") {
            $this->io->note("File `{$this->envPath}` was not found, but some database configuration variables are present. Global system environment variables might be defined. If this is not right, use -f option to force setup to run.");
            return;
        }

        //Goto setup
        $this->setupEnv($input);
    }

    /**
     * Setup the `.env` file.
     *
     * @param  InputInterface $args Command arguments
     * @return void
     */
    public function setupEnv(InputInterface $args)
    {
        $this->io->note("Database and SMTP credentials will be saved in `{$this->envPath}`");

        // Get database info
        $dbParams = $this->askForDatabase($args);

        // Test database
        $this->testDatabase($dbParams);

        // Ask for SMTP info
        $smtpParams = $this->askForSmtp($args);

        // Save env file
        $this->saveFile($dbParams, $smtpParams);

        // At this point, `$this->uf` is still using the old configs.
        // We need to refresh the `db.default` config values
        $newConfig = array_merge($this->ci->config['db.default'], $dbParams);
        $this->ci->config->set("db.default", $newConfig);
    }

    /**
     * Ask for database crendentials
     *
     * @param  InputInterface $args Command arguments
     * @return array The databse credentials
     */
    protected function askForDatabase(InputInterface $args)
    {
        $this->io->section("Setting up database");

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
                'database' => $name
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
                'charset' => $this->ci->config['db.default.charset']
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
     * Ask for SMTP credential
     *
     * @param  InputInterface $args Command arguments
     * @return array The SMTP connection info
     */
    protected function askForSmtp(InputInterface $args)
    {
        // Ask for the smtp values now
        $this->io->section("Email setup");
        $this->io->write("Enter your SMTP server credentials. This is the server used to send emails from the system. Edit `{$this->envPath}` if you have problems sending email later.");

        $smtpHost = ($args->getOption('smtp_host')) ?: $this->io->ask("SMTP Host", "host.example.com");
        $smtpUser = ($args->getOption('smtp_user')) ?: $this->io->ask("SMTP User", "relay@example.com");
        $smtpPassword = ($args->getOption('smtp_password')) ?: $this->io->askHidden("SMTP Password", function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });

        return [
            'host' => $smtpHost,
            'user' => $smtpUser,
            'password' => $smtpPassword
        ];
    }

    /**
     * Write the env file
     *
     * @param  array $dbParams Database params
     * @param  array $smtpParams SMTP params
     * @return void
     */
    protected function saveFile($dbParams, $smtpParams)
    {
        $this->io->section("Saving data");

        // Prepare file content
        if ($dbParams['driver'] == 'sqlite') {
            $fileContent = [
                "UF_MODE=\"\"\n",
                "DB_DRIVER=\"{$dbParams['driver']}\"\n",
                "DB_NAME=\"{$dbParams['database']}\"\n",
                "SMTP_HOST=\"{$smtpParams['host']}\"\n",
                "SMTP_USER=\"{$smtpParams['user']}\"\n",
                "SMTP_PASSWORD=\"{$smtpParams['password']}\"\n"
            ];
        } else {
            $fileContent = [
                "UF_MODE=\"\"\n",
                "DB_DRIVER=\"{$dbParams['driver']}\"\n",
                "DB_HOST=\"{$dbParams['host']}\"\n",
                "DB_PORT=\"{$dbParams['port']}\"\n",
                "DB_NAME=\"{$dbParams['database']}\"\n",
                "DB_USER=\"{$dbParams['username']}\"\n",
                "DB_PASSWORD=\"{$dbParams['password']}\"\n",
                "SMTP_HOST=\"{$smtpParams['host']}\"\n",
                "SMTP_USER=\"{$smtpParams['user']}\"\n",
                "SMTP_PASSWORD=\"{$smtpParams['password']}\"\n"
            ];
        }

        // Let's save this config
        file_put_contents($this->envPath, $fileContent);

        // Success
        $this->io->success("Database and SMTP credentials saved to `{$this->envPath}`");
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
