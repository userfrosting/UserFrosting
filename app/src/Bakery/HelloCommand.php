<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;

/**
 * Sample BAkery command
 * 
 * N.B.: THIS FILE IS SAFE TO EDIT
 */
class HelloCommand extends Command
{
    use WithSymfonyStyle;
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
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
