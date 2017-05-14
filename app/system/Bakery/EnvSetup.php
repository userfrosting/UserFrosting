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
class EnvSetup extends Bakery
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

        // Before testing the db, will try to load the UF Container
        $bakery->getContainer();
        $bakery->testDB(true);
    }

    /**
     * Run the debug script.
     *
     * @access public
     * @return void
     */
    public function run()
    {
        $path = \UserFrosting\APP_DIR. '/.env';
        if (file_exists($path)) {
            $this->io->warning("\nFile `$path` exist already.");
            if (!$this->io->askConfirmation("Overwrite? [y/N] ", false)) {
                exit(1);
            }
        }

        $this->setupEnv();
    }

    /**
     * setupEnv function.
     *
     * @access public
     * @return void
     */
    public function setupEnv()
    {
        $this->io->write("\n<info>Enter your database credentials :</info>");
        $host = $this->io->ask("Hostname (localhost): ", "localhost");
        $port = $this->io->ask("Port (3306): ", "3306");
        $name = $this->io->ask("Database name (userfrosting): ", "userfrosting");
        $user = $this->io->ask("Username (userfrosting): ", "userfrosting");
        $password = $this->io->askAndHideAnswer("Password: ");

        $fileContent = [
            "UF_MODE=\"\"\n",
            "DB_DRIVER=\"mysql\"\n",
            "DB_HOST=\"$host\"\n",
            "DB_PORT=\"$port\"\n",
            "DB_NAME=\"$name\"\n",
            "DB_USER=\"$user\"\n",
            "DB_PASSWORD=\"$password\"\n",
            "SMTP_HOST=\"host.example.com\"\n",
            "SMTP_USER=\"relay@example.com\"\n",
            "SMTP_PASSWORD=\"password\"\n"
        ];

        // Let's save this config
        file_put_contents(\UserFrosting\APP_DIR. '/.env', $fileContent);
    }
}