<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\LocaleCompareCommand;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * Test for LocaleCompareCommand (locale:compare)
 */
class LocaleCompareCommandTest extends TestCase
{
    use Helper\runCommand;

    /**
     * @var string Command to test
     */
    protected $commandToTest = LocaleCompareCommand::class;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Force config to only three locales
        $this->ci->config->set('site.locales.available', [
            'en_US' => true,
            'es_ES' => false,
            'fr_FR' => true,
        ]);

        // Use test locale data
        $this->ci->locator = new ResourceLocator(__DIR__ . '/data');
        $this->ci->locator->registerStream('locale', '', null, true);
    }

    public function testCommandWithArguments(): void
    {
        $result = $this->runCommand([
            '--left'  => 'en_US',
            '--right' => 'fr_FR',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertStringContainsString('Comparing `en_US` with `fr_FR`', $output);
    }

    /**
     * @depends testCommandWithArguments
     */
    public function testCommandWithNoDifferences(): void
    {
        $result = $this->runCommand([
            '--left'  => 'en_US',
            '--right' => 'en_US',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertStringContainsString('Comparing `en_US` with `en_US`', $output);
        $this->assertStringContainsString('No difference between the two locales.', $output);
        $this->assertStringContainsString('No missing keys.', $output);
        $this->assertStringContainsString('No empty values.', $output);
    }

    /**
     * @depends testCommandWithArguments
     */
    public function testCommand(): void
    {
        $result = $this->runCommand([], [
            'en_US',
            'fr_FR',
        ]);
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertStringContainsString('Comparing `en_US` with `fr_FR`', $output);
        $this->assertStringNotContainsString('No difference between the two locales.', $output);
        $this->assertStringNotContainsString('No missing keys.', $output);
        $this->assertStringNotContainsString('No empty values.', $output);
    }
}
