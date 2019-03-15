<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * migrate:refresh Bakery Command.
 * Refresh the database by rolling back the last migrations and running them up again
 *
 * @author Louis Charette
 */
class MigrateRefreshCommand extends MigrateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:refresh')
             ->setDescription('Rollback the last migration operation and run it up again')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of batch to rollback', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration refresh');

        // Get options
        $steps = $input->getOption('steps');

        // Get migrator
        $migrator = $this->setupMigrator($input);

        // Get pending migrations
        $ran = $migrator->getRanMigrations($steps);

        // Don't go further if no migration is ran
        if (empty($ran)) {
            $this->io->success('Nothing to refresh');
            exit(1);
        }

        // Show migrations about to be reset when in production mode
        if ($this->isProduction()) {
            $this->io->section('Migrations to refresh');
            $this->io->listing($ran);

            // Confirm action when in production mode
            if (!$this->confirmToProceed($input->getOption('force'))) {
                exit(1);
            }
        }

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
            $this->io->warning('Nothing was refreshed !');

            return;
        }

        // Run back up again
        $migrated = $migrator->run(['pretend' => false, 'step' => false]);
        $this->io->writeln($migrator->getNotes());

        // If all went well, there's no fatal errors and we have migrated
        // something, show some success
        if (!empty($migrated)) {
            $this->io->success('Refresh successful !');
        }
    }
}
