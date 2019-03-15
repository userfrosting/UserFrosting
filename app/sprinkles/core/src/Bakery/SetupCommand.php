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
 * Setup wizard CLI Tools.
 * Helper command to setup .env file
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup')
             ->setDescription('UserFrosting Configuration Wizard')
             ->setHelp('This command combine the <info>setup:env</info>, <info>setup:db</info> and <info>setup:smtp</info> commands.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('setup:db');
        $command->run($input, $output);

        $command = $this->getApplication()->find('setup:smtp');
        $command->run($input, $output);

        $command = $this->getApplication()->find('setup:env');
        $command->run($input, $output);
    }
}
