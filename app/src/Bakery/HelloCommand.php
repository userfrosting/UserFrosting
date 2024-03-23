<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;

/**
 * Sample Bakery command.
 *
 * N.B.: This file is sage to edit or delete. If you delete this class, don't
 *       forget to delete the corresponding entry in the Sprinkle Recipe!
 */
class HelloCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('hello')
             ->setDescription('Show hello world message');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Hello !');
        $this->io->success('Hello world');

        return self::SUCCESS;
    }
}
