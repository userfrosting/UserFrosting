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

/**
 * Debug CLI Tools.
 * Perform the preflight check for UserFrosting install
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ClearCache extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("clear-cache")
             ->setDescription("Clears the application cache");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Clearing cache");
        $this->ci->cache->flush();
        $this->io->success("Done !");
    }
}