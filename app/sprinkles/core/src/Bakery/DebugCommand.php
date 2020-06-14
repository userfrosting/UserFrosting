<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest;
use UserFrosting\Sprinkle\Core\Bakery\Helper\NodeVersionCheck;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Debug CLI tool.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class DebugCommand extends BaseCommand
{
    use DatabaseTest;
    use NodeVersionCheck;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('debug')
             ->setDescription('Test the UserFrosting installation and setup the database')
             ->setHelp("This command is used to check if the various dependencies of UserFrosting are met and display useful debugging information. \nIf any error occurs, check out the online documentation for more info about that error. \nThis command also provide the necessary tools to setup the database credentials");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title('UserFrosting');

        // Need to touch the config service first to load dotenv values
        $config = $this->ci->config;

        // Perform tasks & display info
        $this->io->definitionList(
            ['UserFrosing version'  => \UserFrosting\VERSION],
            ['OS Name'              => php_uname('s')],
            ['Project Root'         => \UserFrosting\ROOT_DIR],
            ['Environment mode'     => getenv('UF_MODE')],
            ['PHP Version'          => $this->checkPhpVersion()],
            ['Node Version'         => $this->checkNodeVersion()],
            ['NPM Version'          => $this->checkNpmVersion()]
        );

        // Now we list Sprinkles
        $this->listSprinkles($input, $output);

        // Show the DB config
        $this->showConfig();

        // Check database connexion
        $this->checkDatabase();

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success('Ready to bake !');

        // Command return success
        return 0;
    }

    /**
     * Check the minimum version of php.
     * This is done by composer itself, but we do it again for good mesure.
     *
     * @return string The current PHP Version
     */
    protected function checkPhpVersion(): string
    {
        $phpVersion = (string) phpversion();

        if (version_compare($phpVersion, \UserFrosting\PHP_MIN_VERSION, '<')) {
            $this->io->error('UserFrosting requires php version ' . \UserFrosting\PHP_MIN_VERSION . " or above. You'll need to update you PHP version before you can continue.");
            exit(1);
        }

        // Check for deprecated versions
        if (version_compare($phpVersion, \UserFrosting\PHP_RECOMMENDED_VERSION, '<')) {
            $this->io->warning('While your PHP version is still supported by UserFrosting, we recommend version ' . \UserFrosting\PHP_RECOMMENDED_VERSION . ' or above as ' . $phpVersion . ' will soon be unsupported. See http://php.net/supported-versions.php for more info.');
        }

        return $phpVersion;
    }

    /**
     * List all sprinkles defined in the Sprinkles schema file,
     * making sure this file exist at the same time.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function listSprinkles(InputInterface $input, OutputInterface $output): void
    {
        // Check for Sprinkles schema file
        $path = \UserFrosting\SPRINKLES_SCHEMA_FILE;
        if (@file_exists($path) === false) {
            $this->io->error("The file `$path` not found.");
        }

        // List installed sprinkles
        $command = $this->getApplication()->find('sprinkle:list');
        $command->run($input, $output);

        /** @var \UserFrosting\System\Sprinkle\SprinkleManager $sprinkleManager */
        $sprinkleManager = $this->ci->sprinkleManager;

        // Throw fatal error if the `core` sprinkle is missing
        if (!$sprinkleManager->isAvailable('core')) {
            $this->io->error("The `core` sprinkle is missing from the 'sprinkles.json' file.");
            exit(1);
        }
    }

    /**
     * Check the database connexion and setup the `.env` file if we can't
     * connect and there's no one found.
     */
    protected function checkDatabase(): void
    {
        $this->io->title('Testing database connection...');

        try {
            $this->testDB();
            $this->io->writeln('Database connection successful');

            return;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->io->error($error);
            exit(1);
        }
    }

    /**
     * Display database config as for debug purposes.
     */
    protected function showConfig(): void
    {
        // Get config
        $config = $this->ci->config;

        // Display database info
        $this->io->title('Database config');
        $this->io->definitionList(
            ['DRIVER'   => $config['db.default.driver']],
            ['HOST'     => $config['db.default.host']],
            ['PORT'     => $config['db.default.port']],
            ['DATABASE' => $config['db.default.database']],
            ['USERNAME' => $config['db.default.username']],
            ['PASSWORD' => ($config['db.default.password'] ? '*********' : '')]
        );
    }
}
