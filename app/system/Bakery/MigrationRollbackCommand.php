<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\Bakery;
use UserFrosting\System\Bakery\Migrations\Migrator;

/**
 * Migration CLI Tools.
 * Perform database migrations commands
 *
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MigrationRollbackCommand extends Bakery
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate:rollback")
             ->setDescription("Rollback last database migration")
             ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of steps to rollback', 1)
             ->addOption('sprinkle', null, InputOption::VALUE_REQUIRED, 'The sprinkle to rollback', "");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Migration rollback");

        $step = $input->getOption('steps');
        $sprinkle = $input->getOption('sprinkle');

        $migrator = new Migrator($this->io, $this->ci);
        $migrator->runDown($step, $sprinkle);
    }
}