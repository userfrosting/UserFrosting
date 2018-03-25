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
 * migrate:refresh Bakery Command.
 * Refresh the database by rolling back the last migrations and running them up again
 *
 * @author Louis Charette
 */
class MigrateRefreshCommand extends MigrateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate:refresh")
             ->setDescription("Rollback the last migration operation and run it up again")
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of batch to rollback', 1);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Migration refresh");

        // Get options
        $steps = $input->getOption('steps');

        // Get migrator
        $migrator = $this->setupMigrator($input);

        // Rollback migration
        try {
            $rolledback = $migrator->rollback(['pretend' => false, 'steps' => $steps]);
        } catch (\Exception $e) {
            $this->io->writeln($migrator->getNotes());
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Get notes and display them
        $this->io->writeln($migrator->getNotes());

        // Stop if nothing was rolledback
        if (empty($rolledback)) {
            $this->io->success("Nothing to refresh");
            return;
        }

        // Run back up again
        $migrated = $migrator->run(['pretend' => false, 'step' => false]);
        $this->io->writeln($migrator->getNotes());

        // If all went well, there's no fatal errors and we have migrated
        // something, show some success
        if (empty($migrated)) {
            $this->io->success("Nothing to refresh");
        } else {
            $this->io->success("Refresh successful !");
        }
    }
}
