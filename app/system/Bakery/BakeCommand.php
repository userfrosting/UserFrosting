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

/**
 * Bake command.
 * Shortcut to run multiple commands at once
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class BakeCommand extends Bakery
{
    /**
     * @var string Path to the build/ directory
     */
    protected $buildPath;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("bake")
             ->setDescription("UserFrosting installation command")
             ->setHelp("This command combine the <info>debug</info>, <info>migrate</info> and <info>build-assets</info> commands.");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('setup');
        $command->run($input, $output);

        $command = $this->getApplication()->find('debug');
        $command->run($input, $output);

        $command = $this->getApplication()->find('migrate');
        $command->run($input, $output);

        $command = $this->getApplication()->find('build-assets');
        $command->run($input, $output);
    }
}