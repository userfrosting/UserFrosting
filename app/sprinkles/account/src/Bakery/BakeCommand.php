<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Bakery\BakeCommand as CoreBakeCommand;

/**
 * Bake command extension.
 * Adding Account provided `create-admin` to the bake command.
 */
class BakeCommand extends CoreBakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected function executeConfiguration(InputInterface $input, OutputInterface $output)
    {
        parent::executeConfiguration($input, $output);

        $command = $this->getApplication()->find('create-admin');
        $command->run($input, $output);
    }
}
