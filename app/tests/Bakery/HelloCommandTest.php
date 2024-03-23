<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\App\Controller;

use UserFrosting\App\Bakery\HelloCommand;
use UserFrosting\App\MyApp;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\TestCase;

/**
 * Test for HelloCommand bakery command.
 *
 * N.B.: This file is sage to edit or delete.
 */
class HelloCommandTest extends TestCase
{
    protected string $mainSprinkle = MyApp::class;

    public function testCommand(): void
    {
        /** @var HelloCommand */
        $command = $this->ci->get(HelloCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Hello world', $result->getDisplay());
    }
}
