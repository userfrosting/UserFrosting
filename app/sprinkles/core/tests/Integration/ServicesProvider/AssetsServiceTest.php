<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Assets\Assets;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `assets` service.
 * Check to see if service returns what it's supposed to return
 * 
 * @todo Need to test the actual output. We know an instance is returned, but
 * we don't necessary know it returns the correct streams and whatnot
 */
class AssetsServiceTest extends TestCase
{
    public function testServiceWithRawAssets()
    {
        $this->ci->config['assets.use_raw'] = true;
        $this->assertInstanceOf(Assets::class, $this->ci->assets);
    }

    public function testServiceWithCompiledAssets()
    {
        $this->ci->config['assets.use_raw'] = false;
        $this->assertInstanceOf(Assets::class, $this->ci->assets);
    }
}
