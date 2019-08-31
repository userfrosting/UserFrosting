<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Account\Repository\VerificationRepository;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `repoVerification` service.
 * Check to see if service returns what it's supposed to return
 */
class RepoVerificationServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(VerificationRepository::class, $this->ci->repoVerification);
    }
}
