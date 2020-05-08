<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use Mockery as m;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Tests\TestCase;

/**
 * Tests UserController
 * Specific tests for createUser, which fails on pgsql because of a bug when
 * creating a user with specific id before calling create
 */
class UserControllerCreateTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;
    use withController;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testCreate()
    {
        $fm = $this->ci->factory;

        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Create will fail on PGSQL if a user is created with forced id
        // because it mess the auto_increment
        // @see https://stackoverflow.com/questions/36157029/laravel-5-2-eloquent-save-auto-increment-pgsql-exception-on-same-id
        $user = $fm->create(User::class);
        $this->giveUserTestPermission($user, 'create_user');
        $this->giveUserTestPermission($user, 'create_user_field');
        $this->loginUser($user);

        // Recreate controller to use the fake mailer
        $controller = $this->getController();

        // Create a fake group
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Set post data
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com',
            'group_id'   => $group->id,
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was created
        $user = User::where('user_name', 'foo')->first();
        $this->assertSame('foo name', $user->first_name);
        $this->assertSame($group->id, $user->group->id);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithNoLocale()
    {
        $this->ci->config['site.locales.available'] = [];
        $this->testCreate();
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithNoGroupId()
    {
        $fm = $this->ci->factory;

        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Also create a group
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Create will fail on PGSQL if a user is created with forced id
        // because it mess the auto_increment
        // @see https://stackoverflow.com/questions/36157029/laravel-5-2-eloquent-save-auto-increment-pgsql-exception-on-same-id
        $user = $fm->create(User::class, [
            'group_id' => $group->id,
        ]);
        $this->giveUserTestPermission($user, 'create_user');
        $this->loginUser($user);

        // Recreate controller to use the fake mailer
        $controller = $this->getController();

        // Set post data
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was created
        $user = User::where('user_name', 'foo')->first();
        $this->assertSame('foo name', $user->first_name);
        $this->assertSame($group->id, $user->group->id);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @return UserController
     */
    protected function getController()
    {
        return new UserController($this->ci);
    }
}
