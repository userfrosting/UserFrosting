<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\LocaleInfoCommand;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * Test for LocaleInfoCommand (locale:info)
 */
class LocaleInfoCommandTest extends TestCase
{
    use Helper\runCommand;

    /**
     * @var string Command to test
     */
    protected $commandToTest = LocaleInfoCommand::class;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Force config to only three locales
        $this->ci->config->set('site.locales.available', [
            'en_US' => true,
            'es_ES' => false,
            'fr_FR' => true,
        ]);

        // Use test locale data
        $this->ci->locator = new ResourceLocator(__DIR__.'/data');
        $this->ci->locator->registerStream('locale', '', null, true);
    }

    /**
     * Test base command without any arguments
     */
    public function testCommand(): void
    {
        $result = $this->runCommand();
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertNotContains('Spanish', $output);
        $this->assertContains('English', $output);
    }
}
