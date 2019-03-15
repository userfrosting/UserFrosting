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
use UserFrosting\System\Bakery\BaseCommand;

/**
 * seed Bakery Command
 * Perform a database seed
 *
 * @author Louis Charette
 */
class SeedListCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('seed:list')
             ->setDescription('List all database seeds available')
             ->setHelp('This command returns a list of database seeds that can be called using the `seed` command.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Database Seeds List');
        $seeds = $this->ci->seeder->getSeeds();
        $this->io->table(['Name', 'Namespace', 'Sprinkle'], $seeds);
    }
}
