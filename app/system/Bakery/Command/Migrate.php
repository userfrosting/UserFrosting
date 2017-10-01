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
class Migrate extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate")
             ->setDescription("Perform database migration")
             ->setHelp("This command runs all the pending database migrations.")
             ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Run migrations in "dry run" mode');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Migrator");

        $pretend = $input->getOption('pretend');

        $migrator = new Migrator($this->io, $this->ci);
        $migrator->runUp($pretend);
    }
}