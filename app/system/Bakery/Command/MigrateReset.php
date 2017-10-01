<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\System\Bakery\Migrator;

/**
 * Migrate CLI Tools.
 * Perform database migrations commands
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MigrateReset extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate:reset")
             ->setDescription("Reset the whole database to an empty state")
             ->addOption('sprinkle', null, InputOption::VALUE_REQUIRED, 'The sprinkle to rollback', "")
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run migrations in "dry run" mode');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Migration reset");

        $sprinkle = $input->getOption('sprinkle');
        $pretend = $input->getOption('pretend');

        $migrator = new Migrator($this->io, $this->ci);
        $migrator->runDown(-1, $sprinkle, $pretend);
    }
}