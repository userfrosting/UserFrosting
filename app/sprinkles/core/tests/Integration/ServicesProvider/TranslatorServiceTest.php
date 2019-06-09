<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `translator` service.
 * Check to see if service returns what it's supposed to return
 */
class TranslatorServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(MessageTranslator::class, $this->ci->translator);
    }
}
