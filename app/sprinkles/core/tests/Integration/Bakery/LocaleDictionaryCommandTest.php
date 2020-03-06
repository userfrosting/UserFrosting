<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\LocaleDictionaryCommand;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * Test for LocaleDictionaryCommand (locale:dictionary)
 */
class LocaleDictionaryCommandTest extends TestCase
{
    use Helper\runCommand;

    /**
     * @var string Command to test
     */
    protected $commandToTest = LocaleDictionaryCommand::class;

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
}
