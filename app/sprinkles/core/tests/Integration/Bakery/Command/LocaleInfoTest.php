<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery\Command;

use UserFrosting\Sprinkle\Core\Bakery\Command\LocaleInfo;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * Test for LocaleInfoCommand (locale:info)
 */
class LocaleInfoTest extends TestCase
{
    use Helper\runCommand;

    /**
     * @var string Command to test
     */
    protected $commandToTest = LocaleInfo::class;

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

    /**
     * Test base command without any arguments
     */
    public function testCommand(): void
    {
        $result = $this->runCommand();
        $this->assertSame(0, $result->getStatusCode());

        $output = $result->getDisplay();
        $this->assertStringNotContainsString('Spanish', $output);
        $this->assertStringContainsString('English', $output);
    }
}
