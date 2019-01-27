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
 * Bake command.
 * Shortcut to run multiple commands at once
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class BakeCommand extends BaseCommand
{
    /**
     * @var string The UserFrosting ASCII art.
     */
    public $title = "
 _   _              ______             _   _
| | | |             |  ___|           | | (_)
| | | |___  ___ _ __| |_ _ __ ___  ___| |_ _ _ __   __ _
| | | / __|/ _ \ '__|  _| '__/ _ \/ __| __| | '_ \ / _` |
| |_| \__ \  __/ |  | | | | | (_) \__ \ |_| | | | | (_| |
 \___/|___/\___|_|  \_| |_|  \___/|___/\__|_|_| |_|\__, |
                                                    __/ |
                                                   |___/";

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('bake')
             ->setDescription('UserFrosting installation command')
             ->setHelp('This command combine the <info>setup:db</info>, <info>setup:smtp</info>, <info>debug</info>, <info>migrate</info>, <info>create-admin</info> and <info>build-assets</info> commands.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->writeln("<info>{$this->title}</info>");

        $command = $this->getApplication()->find('setup:db');
        $command->run($input, $output);

        $command = $this->getApplication()->find('setup:smtp');
        $command->run($input, $output);

        $command = $this->getApplication()->find('debug');
        $command->run($input, $output);

        $command = $this->getApplication()->find('migrate');
        $command->run($input, $output);

        $command = $this->getApplication()->find('create-admin');
        $command->run($input, $output);

        $command = $this->getApplication()->find('build-assets');
        $command->run($input, $output);

        $command = $this->getApplication()->find('clear-cache');
        $command->run($input, $output);
    }
}
