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

/**
 * migrate:rollback Bakery Command
 * Rollback the last migrations ran against the database.
 *
 * @author Louis Charette
 */
class MigrateCleanCommand extends MigrateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:clean')
             ->setDescription('Clean stale records from migrations database')
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
        //    print_r($stale);

        $this->cleanStaleRecords($stale, $migrator);

        // Display ran migrations
        $this->io->section('Cleaned migrations');
        if ($ran->count() > 0) {
        } else {
            $this->io->note('No installed migrations');
        }
    }

    protected function cleanStaleRecords(array $stale, $migrator)
    {
        //  print_r($stale);
        foreach ($stale as $staleFile) {
            print_r($staleFile);
            print_r($staleFile['migration']);
            $migrator->$repository->delete($staleFile['migration']);
        }
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
        })->toArray();
    }
}
