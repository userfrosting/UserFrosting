<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery\Helper;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Helper method to test a Bakery command
 */
trait runCommand
{
    /**
     * @param string[] $input     Command arguments and options
     * @param string[] $userInput User Interactions
     *
     * @see https://symfony.com/doc/4.4/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    protected function runCommand(array $input = [], array $userInput = []): CommandTester
    {
        // Create the app, create the command and add the command to the app
        $app = new Application();
        $command = new $this->commandToTest();
        $command->setContainer($this->ci);
        $app->add($command);

        // Add the command to the input to create the execute argument
        $execute = array_merge([
            'command' => $command->getName(),
        ], $input);

        // Execute command tester
        $commandTester = new CommandTester($command);

        if (!empty($userInput)) {
            $commandTester->setInputs($userInput);
        }

        $commandTester->execute($execute);

        return $commandTester;
    }
}
