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

use Slim\App;
use Slim\Container;

/**
 * Generate a list of a projects routes.
 *
 * @author Jose Vasconcellos
 */
class Routes extends BaseCommand
{
    /**
     * @var string Path to the build/ directory
     */
    protected $app;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
       $this->setName("routes")
             ->setDescription("Dumps all routes.");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $app;

        $this->ci->settings = $this->ci->config['settings'];
        $this->app = new App($this->ci);
        $app = $this->app;

        $this->io->title("Generate routes");
        $routePaths = array_reverse($this->ci->locator->findResources('routes://', true, true));
        $routeDirCount=count($routePaths);
        $routeCount=0;
        foreach ($routePaths as $path) {
            $routeFiles = glob($path . '/*.php');
            foreach ($routeFiles as $routeFile) {
                $routeCount += 1;
                //$this->io->writeln("<info>$routeFile</info>");
                require_once $routeFile;
            }
        }

        $allRoutes = $app->getContainer()->get('router')->getRoutes();
        foreach ($allRoutes as $route) {
            $p = $route->getPattern();
            foreach ($route->getMethods() as $m)
                $this->io->writeln($m.' '.$p);
        }

        $this->io->success("Routes generated from {$routeCount} files!");
    }
}
