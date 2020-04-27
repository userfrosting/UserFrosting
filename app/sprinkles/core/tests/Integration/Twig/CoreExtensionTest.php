<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig;

use UserFrosting\Tests\TestCase;

/**
 * CoreExtensionTest class.
 * Tests Core twig extentions
 */
class CoreExtensionTest extends TestCase
{
    /**
     * @see https://github.com/userfrosting/UserFrosting/issues/1090
     */
    public function testTranslateFunction(): void
    {
        $result = $this->ci->view->fetchFromString('{{ translate("USER", 2) }}');
        $this->assertSame('Users', $result);
    }
}
