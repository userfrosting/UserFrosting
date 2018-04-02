<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Database\Seeder\SeederLocator;
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
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('seed:list')
             ->setDescription('List all database seeds available')
             ->setHelp('This command returns a list of database seeds that can be called using the `seed` command.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Database Seeds List");
        $locator = new SeederLocator($this->ci->sprinkleManager, new Filesystem);
        $seeds = $locator->getSeeders();
        $this->io->table(['Name', 'Namespace', 'Sprinkle'], $seeds->toArray());
    }
}
