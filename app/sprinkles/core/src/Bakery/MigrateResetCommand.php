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
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;

/**
 * migrate:reset Bakery Command
 * Reset the database to a clean state
 *
 * @author Louis Charette
 */
class MigrateResetCommand extends MigrateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate:reset")
             ->setDescription("Reset the whole database to an empty state, rolling back all migrations.")
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run migrations in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.')
             ->addOption('hard', null, InputOption::VALUE_NONE, 'Force drop all tables in the database, even if they were not created or listed by the migrator')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Migration reset");

        // Check if the hard option is used
        if ($input->getOption('hard')) {
            $this->performHardReset($input);
        } else {
            $this->performReset($input);
        }
    }

    /**
     *    Reset the whole database to an empty state by rolling back all migrations
     *
     *    @param InputInterface $input
     */
    protected function performReset(InputInterface $input)
    {
        // Get options
        $pretend = $input->getOption('pretend');

        // Get migrator
        $migrator = $this->setupMigrator($input);

        // Reset migrator
        try {
            $resetted = $migrator->reset($pretend);
        } catch (\Exception $e) {
            $this->io->writeln($migrator->getNotes());
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Get notes and display them
        $this->io->writeln($migrator->getNotes());

        // Delete the repository
        if (!$pretend && $migrator->repositoryExists()) {
            $this->io->writeln("<info>Deleting migration repository</info>");
            $migrator->getRepository()->deleteRepository();
        }

        // If all went well, there's no fatal errors and we have migrated
        // something, show some success
        if (empty($resetted)) {
            $this->io->success("Nothing to reset");
        } else {
            $this->io->success("Reset successful !");
        }
    }

    /**
     *    Hard reset the whole database to an empty state by dropping all tables
     *
     *    @param InputInterface $input
     */
    protected function performHardReset(InputInterface $input)
    {
        // Get current connection
        $database = ($input->getOption('database')) ?: $this->ci->db->getDatabaseManager()->getDefaultConnection();

        // Confirm action
        $this->io->warning("This will drop all existing tables from the `$database` database, including tables not managed by bakery. All data will be lost! You have been warned!");
        if (!$this->io->confirm('Do you really wish to run this command?', false)) {
            $this->io->comment('Command Cancelled!');
            exit(1);
        }

        // Get shema Builder
        $connection = $this->ci->db->connection($database);
        $schema = $connection->getSchemaBuilder();

        // Get a list of all tables
        $tables = $connection->select('SHOW TABLES');
        foreach (array_map('reset', $tables) as $table) {
            $this->io->writeln("Dropping table `$table`...");

            // Perform drop
            $schema->drop($table);
        }

        $this->io->success("Hard reset successful !");
    }
}
