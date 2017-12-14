<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Interop\Container\ContainerInterface;

/**
 * Base class for UserFrosting Bakery CLI tools.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class BaseCommand extends Command
{
    /**
     * @var @Symfony\Component\Console\Style\SymfonyStyle
     * See http://symfony.com/doc/current/console/style.html
     */
    protected $io;

    /**
     * @var string Path to the project root folder
     */
    protected $projectRoot;

    /**
     * @var ContainerInterface $ci The global container object, which holds all of the UserFrosting services.
     */
    protected $ci;

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->projectRoot = \UserFrosting\ROOT_DIR;
    }

    /**
     * Setup the global container object
     */
    public function setContainer(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }
}
