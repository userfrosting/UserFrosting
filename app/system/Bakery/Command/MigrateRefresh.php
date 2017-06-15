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
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MigrateRefresh extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate:refresh")
             ->setDescription("Rollback the last migration operation and run it up again")
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of steps to rollback', 1)
             ->addOption('sprinkle', null, InputOption::VALUE_REQUIRED, 'The sprinkle to rollback', "")
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run migrations in "dry run" mode');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Migration refresh");

        $step = $input->getOption('steps');
        $sprinkle = $input->getOption('sprinkle');
        $pretend = $input->getOption('pretend');

        $migrator = new Migrator($this->io, $this->ci);
        $migrator->runDown($step, $sprinkle, $pretend);
        $migrator->runUp($pretend);
    }
}