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
use UserFrosting\Sprinkle\Core\Bakery\LocaleInfoCommand;
use UserFrosting\Tests\TestCase;

/**
 * LocaleMissingKeysCommand Test
 *
 * @author Louis Charette
 */
class LocaleInfoCommandTest extends TestCase
{
    /**
     * Test base command without any arguments
     */
    public function testCommand(): void
    {
        $result = $this->runCommand();
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertNotContains('Français', $output);
        $this->assertContains('English', $output);
    }

    /**
     * @param string[] $input
     */
    protected function runCommand(array $input = []): CommandTester
    {
        // Force config to only three locales
        $this->ci->config->set('site.locales.available', [
            'en_US' => true,
            'es_ES' => true,
            'fr_FR' => false,
        ]);

        // Create the app, create the command and add the command to the app
        $app = new Application();
        $command = new LocaleInfoCommand();
        $command->setContainer($this->ci);
        $app->add($command);

        // Add the command to the input to create the execute argument
        $execute = array_merge([
            'command' => $command->getName(),
        ], $input);

        // Execute command tester
        $commandTester = new CommandTester($command);
        $commandTester->execute($execute);

        return $commandTester;
    }
}
