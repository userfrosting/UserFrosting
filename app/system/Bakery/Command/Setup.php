<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Command;

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
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Setup extends BaseCommand
{
    /**
     * envfile path
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
             ->addOption("force", "f", InputOption::VALUE_NONE, "If `.env` file exist, force setup to run again");
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
            $this->io->note("File `{$this->envPath}` was not found, but some database configuration are present. Global system environment variable might be defined. If this is not right, use -f option to force setup to run.");
            return;
        }

        //Goto setup
        $this->setupEnv();
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

        // Get the db driver choices
        $drivers = $this->databaseDrivers();


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
        $password = $this->io->askHidden("Password", function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });

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
            $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}':".PHP_EOL;
            $message .= "Exception: " . $e->getMessage() . PHP_EOL.PHP_EOL;
            $message .= "Please check your database configuration and/or google the exception shown above and run command again.";
            $this->io->error($message);
            exit(1);
        }

        // Ask for the smtp values now
        $this->io->section("Enter your SMTP credentials");
        $this->io->write("This is use to send emails from the system. Edit `app/.env` if you have problem with this later.");
        $smtpHost = $this->io->ask("SMTP Host", "host.example.com");
        $smtpUser = $this->io->ask("SMTP User", "relay@example.com");
        $smtpPassword = $this->io->askHidden("SMTP Password", function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });

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
        file_put_contents($this->envPath, $fileContent);

        // At this point, `$this->uf` is still using the old configs.
        // We need to refresh the `db.default` config values
        $newConfig = array_merge($config['db.default'], $dbParams);
        $this->ci->config->set("db.default", $newConfig);
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
}