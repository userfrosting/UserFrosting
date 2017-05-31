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
class MigrationCommand extends Bakery
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("migrate")
             ->setDescription("Perform database migration")
             ->setHelp("This command runs all the pending database migrations.");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Migration");

        $migrator = new Migrator($this->io, $this->ci);
        $migrator->runUp();
    }
}