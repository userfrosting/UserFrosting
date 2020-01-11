<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * migrate:clean Bakery Command
 * Remove stale migrations from the database.
 *
 * @author Amos Folz
 */
class MigrateCleanCommand extends MigrateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:clean')
             ->setDescription('Remove stale migrations from the database.')
             ->setHelp('Removes stale migrations, which are simply migration class files that have been removed from the Filesystem. E.g. if you run a migration and then delete the migration class file prior to running `down()` for that migration it becomes stale. If a migration is a dependency of another migration you probably want to try to restore the files instead of running this command to avoid further issues.')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not prompt for confirmation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration clean');

        // Get migrator
        $migrator = $this->ci->migrator;

        // Set connection to the selected database
        $migrator->setConnection($input->getOption('database'));

        // Get ran migrations. If repository doesn't exist, there's no ran
        if (!$migrator->repositoryExists()) {
            $ran = collect();
        } else {
            $ran = $migrator->getRepository()->getMigrations();
        }

        // Get available migrations
        $available = $migrator->getAvailableMigrations();

        $stale = $this->getStaleRecords($ran, $available);

        if ($stale->count() > 0) {
            if (!$input->getOption('force')) {
                $this->io->section('Stale migrations');
                $this->io->listing($stale->toArray());

                if (!$this->io->confirm('Continue and remove stale migrations?', false)) {
                    exit;
                }
            }
            $this->io->section('Cleaned migrations');
            $this->cleanStaleRecords($stale, $migrator);
            $this->io->listing($stale->toArray());
        } else {
            $this->io->note('No stale migrations');
        }
    }

    /**
     * Delete stale migrations from the database.
     *
     * @param Collection $stale    Collection of stale migartion classes.
     * @param Migrator   $migrator Migrator object
     */
    protected function cleanStaleRecords(Collection $stale, Migrator $migrator)
    {
        $migrationRepository = $migrator->getRepository();

        //Delete the stale migration classes from the database.
        $stale->each(function ($class) use ($migrationRepository) {
            $migrationRepository->delete($class);
        });
    }

    /**
     * Return an array of stale migrations.
     * A migration is stale if not found in the available stack (class is not in the Filesystem).
     *
     * @param Collection $ran       The ran migrations
     * @param array      $available The available migrations
     *
     * @return Collection Collection of stale migration classes.
     */
    protected function getStaleRecords(Collection $ran, array $available)
    {
        return  $filtered = collect($ran)->filter(function ($migration) use ($available) {
            return !in_array($migration->migration, $available);
        })->pluck('migration');
    }
}
