<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System\Bakery;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base class for UserFrosting Bakery CLI tools.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class BaseCommand extends Command
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     *
     * @see http://symfony.com/doc/current/console/style.html
     */
    protected $io;

    /**
     * @var ContainerInterface The global container object, which holds all of UserFrosting services.
     */
    protected $ci;

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Setup the global container object.
     *
     * @param ContainerInterface $ci
     */
    public function setContainer(ContainerInterface $ci): void
    {
        $this->ci = $ci;
    }

    /**
     *    Return if the app is in production mode.
     *
     *    @return bool True/False if the app is in production mode
     */
    protected function isProduction(): bool
    {
        // Need to touch the config service first to load dotenv values
        $config = $this->ci->config;
        $mode = getenv('UF_MODE') ?: '';

        return $mode === 'production';
    }
}
