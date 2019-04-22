<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\I18n\LocalePathBuilder;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `localePathBuilder` service.
 * Check to see if service returns what it's supposed to return
 *
 * @todo Test the `hasHeader` param
 */
class LocalePathBuilderServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(LocalePathBuilder::class, $this->ci->localePathBuilder);
    }

    public function testServiceWithUnexpectedValueExceptionOnNonStringConfig()
    {
        $this->ci->config['site.locales.default'] = [];
        $this->expectException(\UnexpectedValueException::class);
        $this->ci->localePathBuilder;
    }

    public function testServiceWithUnexpectedValueExceptionOnEmptyStringConfig()
    {
        $this->ci->config['site.locales.default'] = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->ci->localePathBuilder;
    }
}
