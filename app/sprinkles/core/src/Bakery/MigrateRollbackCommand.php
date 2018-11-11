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
use Symfony\Component\Console\Input\InputOption;

/**
 * migrate:rollback Bakery Command
 * Rollback the last migrations ran against the database
 *
 * @author Louis Charette
 */
class MigrateRollbackCommand extends MigrateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:rollback')
             ->setDescription('Rollback last database migration')
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run migrations in "dry run" mode.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('migration', 'm', InputOption::VALUE_REQUIRED, 'The specific migration to rollback.')
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of batch to rollback.', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration rollback');

        // Get options
        $steps = $input->getOption('steps');
        $pretend = $input->getOption('pretend');
        $migration = $input->getOption('migration');

        // Get migrator
        $migrator = $this->setupMigrator($input);

        // Rollback migrations
        try {
            // If we have a specific to rollback, do this. Otherwise, do a normal rollback
            if ($migration) {
                $migrated = $migrator->rollbackMigration($migration, ['pretend' => $pretend]);
            } else {
                $migrated = $migrator->rollback(['pretend' => $pretend, 'steps' => $steps]);
            }
        } catch (\Exception $e) {
            $this->io->writeln($migrator->getNotes());
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Get notes and display them
        $this->io->writeln($migrator->getNotes());

        // If all went well, there's no fatal errors and we have migrated
        // something, show some success
        if (empty($migrated)) {
            $this->io->success('Nothing to rollback');
        } else {
            $this->io->success('Rollback successful !');
        }
    }
}
