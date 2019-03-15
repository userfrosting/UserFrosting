<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Slim\Route;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * route:list Bakery Command
 * Generate a list all registered routes
 * Inspired by Laravel `route:list` artisan command
 *
 * @author Jose Vasconcellos
 * @author Louis Charette
 */
class RouteListCommand extends BaseCommand
{
    /**
     * @var array The table header
     */
    protected $headers = ['Method', 'URI', 'Name', 'Action'];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('route:list')
             ->setDescription('Generate a list all registered routes')
             ->addOption('method', null, InputOption::VALUE_REQUIRED, 'Filter the routes by method.')
             ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Filter the routes by name.')
             ->addOption('uri', null, InputOption::VALUE_REQUIRED, 'Filter the routes by uri.')
             ->addOption('reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes.')
             ->addOption('sort', null, InputOption::VALUE_REQUIRED, 'The column (method, uri, name, action) to sort by.', 'uri');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Registered Routes');

        // Get routes list
        $routes = $this->ci->router->getRoutes();

        // If not route, don't go further
        if (count($routes) === 0) {
            return $this->io->error("Your application doesn't have any routes.");
        }

        // Compile the routes into a displayable format
        $routes = collect($routes)->map(function ($route) use ($input) {
            return $this->getRouteInformation($route, $input);
        })->all();

        // Apply sort
        if ($sort = $input->getOption('sort')) {
            $routes = $this->sortRoutes($sort, $routes);
        }

        // Apply reverse if required
        if ($input->getOption('reverse')) {
            $routes = array_reverse($routes);
        }

        // Display routes
        $this->io->table($this->headers, array_filter($routes));
    }

    /**
     * Get the route information for a given route.
     *
     * @param  Route          $route
     * @param  InputInterface $input [description]
     * @return array
     */
    protected function getRouteInformation(Route $route, InputInterface $input)
    {
        return $this->filterRoute([
            'method' => implode('|', $route->getMethods()),
            'uri'    => $route->getPattern(),
            'name'   => $route->getName(),
            'action' => $route->getCallable(),
        ], $input);
    }

    /**
     * Sort the routes by a given element.
     *
     * @param  string $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Filter the route by URI and / or name.
     *
     * @param  array          $route
     * @param  InputInterface $input [description]
     * @return array|null
     */
    protected function filterRoute(array $route, InputInterface $input)
    {
        if (($input->getOption('name') && !Str::contains($route['name'], $input->getOption('name'))) ||
             $input->getOption('uri') && !Str::contains($route['uri'], $input->getOption('uri')) ||
             $input->getOption('method') && !Str::contains($route['method'], strtoupper($input->getOption('method')))) {
            return;
        }

        return $route;
    }
}
