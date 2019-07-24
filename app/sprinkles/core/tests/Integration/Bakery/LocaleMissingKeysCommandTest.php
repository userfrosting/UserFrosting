<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\LocaleMissingKeysCommand;
use UserFrosting\Tests\TestCase;

/**
 * LocaleMissingKeysCommand Test
 *
 * @author Louis Charette
 */
class LocaleMissingKeysCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Test base command without any arguments
     */
    public function testCommand()
    {
        $result = $this->runCommand();

        $output = $result->getDisplay();
        $this->assertContains('Locales to check: |es_ES|fr_FR|', $output);
        $this->assertContains('Missing keys found successfully', $output);
    }

    /**
     * @depends testCommand
     */
    public function testCommandWithCheckArgument()
    {
        $result = $this->runCommand([
            '--check' => 'fr_FR',
        ]);

        $output = $result->getDisplay();
        $this->assertContains('Locales to check: |fr_FR|', $output);
        $this->assertContains('app/sprinkles/core/tests/Integration/Bakery/data/locale/fr_FR/foo/bar.php', $output);
        $this->assertContains('FOO.BAR', $output);
        $this->assertContains('Missing keys found successfully', $output);
    }

    /**
     * @depends testCommandWithCheckArgument
     */
    public function testCommandWithCheckArgumentNoMissingKeys()
    {
        $result = $this->runCommand([
            '--check' => 'es_ES',
        ]);

        $output = $result->getDisplay();
        $this->assertContains('Locales to check: |es_ES|', $output);
        $this->assertContains('No missing keys found!', $output);
    }

    /**
     * @param array $input
     */
    protected function runCommand($input = [])
    {
        // Replace default locale locator stream with the test data
        $this->ci->locator->removeStream('locale');
        $this->ci->locator->registerStream('locale', '', 'tests/Integration/Bakery/data/locale');

        // Force config to only three locales
        $this->ci->config->set('site.locales.available', [
            'en_US' => 'English',
            'es_ES' => 'Español',
            'fr_FR' => 'Français',
        ]);

        // Create the app, create the command and add the command to the app
        $app = new Application();
        $command = new LocaleMissingKeysCommand();
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
