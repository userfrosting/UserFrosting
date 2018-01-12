<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;

/**
 * ClearCache CLI Command.
 *
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
             ->setDescription("Clears the application cache. Includes cache service, Twig and Router cached data");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Clearing cache");

        // Clear normal cache
        $this->io->writeln("<info> > Clearing Illuminate cache instance</info>", OutputInterface::VERBOSITY_VERBOSE);
        $this->clearIlluminateCache();

        // Clear Twig cache
        $this->io->writeln("<info> > Clearing Twig cached data</info>", OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearTwigCache()) {
            $this->io->error("Failed to clear Twig cached data. Make sure you have write access to the `app/cache/twig` directory.");
            exit(1);
        }

        // Clear router cache
        $this->io->writeln("<info> > Clearing Router cache file</info>", OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearRouterCache()) {
            $file = $this->ci->config['settings.routerCacheFile'];
            $this->io->error("Failed to delete Router cache file. Make sure you have write access to the `$file` file.");
            exit(1);
        }

        $this->io->success("Cache cleared !");
    }

    /**
     * Flush the cached data from the cache service
     *
     * @return void
     */
    protected function clearIlluminateCache()
    {
        $this->ci->cache->flush();
    }

    /**
     * Clear the Twig cache using the Twig CacheHelper class
     *
     * @return bool true/false if operation is successfull
     */
    protected function clearTwigCache()
    {
        $cacheHelper = new CacheHelper($this->ci);
        return $cacheHelper->clearCache();
    }

    /**
     * Clear the Router cache data file
     *
     * @return bool true/false if operation is successfull
     */
    protected function clearRouterCache()
    {
        return $this->ci->router->clearCache();
    }
}