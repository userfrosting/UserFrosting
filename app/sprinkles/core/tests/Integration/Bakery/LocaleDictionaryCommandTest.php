<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\LocaleDictionaryCommand;
use UserFrosting\Tests\TestCase;

/**
 * LocaleMissingKeysCommand Test
 *
 * @author Louis Charette
 */
class LocaleDictionaryCommandTest extends TestCase
{
    public function testCommandWithArguments(): void
    {
        $result = $this->runCommand([
            '--locale' => 'fr_FR',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertNotContains('Dictionary for English locale', $output);
        $this->assertContains('Dictionary for French locale', $output);
    }

    public function testCommand(): void
    {
        $result = $this->runCommand([], [
            'fr_FR',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertNotContains('Dictionary for English locale', $output);
        $this->assertContains('Dictionary for French locale', $output);
    }

    /**
     * @param string[] $input     Command arguments and options
     * @param string[] $userInput User Interactions
     *
     * @see https://symfony.com/doc/4.4/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    protected function runCommand(array $input = [], array $userInput = []): CommandTester
    {
        // Force config to only three locales
        $this->ci->config->set('site.locales.available', [
            'en_US' => true,
            'es_ES' => false,
            'fr_FR' => true,
        ]);

        // Create the app, create the command and add the command to the app
        $app = new Application();
        $command = new LocaleDictionaryCommand();
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
