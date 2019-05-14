<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

use UserFrosting\Tests\TestCase;

/**
 * Special TestCase for Controller Tests
 *
 * @see https://akrabat.com/testing-slim-framework-actions/
 * @author Louis Charette
 * @deprecated Use `withController` Trait instead
 */
class ControllerTestCase extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withController;

    /**
     * Setup test database for controller tests
     */
    public function setUp()
    {
        parent::setUp();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }
}
