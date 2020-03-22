<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Unit;

use Psr\Container\ContainerInterface;
use Mockery as m;
use UserFrosting\Tests\TestCase;
use UserFrosting\System\Sprinkle\SprinkleManager;

class SprinkleManagerTest extends TestCase
{
    /** @var ContainerInterface $fakeCi Our mocked CI used for testing */
    protected $fakeCi;

    public function setUp(): void
    {
        // We don't call parent function to cancel CI creation and get accurate test coverage
        // Run only this test for accurage coverage report on SprinkleManager
        $this->fakeCi = m::mock(ContainerInterface::class);
        $this->fakeCi->eventDispatcher = new eventDispatcherStub();
        $this->fakeCi->locator = new ResourceLocatorStub();

        // Setup test sprinkle mock class so it can be found by `class_exist`
        m::mock('UserFrosting\Sprinkle\Test\Test');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return SprinkleManager
     */
    public function testConstructor()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->assertInstanceOf(SprinkleManager::class, $sprinkleManager);

        return $sprinkleManager;
    }

    /**
     * @depends testConstructor
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSetSprinklesPath(SprinkleManager $sprinkleManager)
    {
        $sprinkleManager->setSprinklesPath('/foo');
        $this->assertSame('/foo', $sprinkleManager->getSprinklesPath());
    }

    /**
     * @depends testConstructor
     */
    public function testLoadSprinkleWithNonExistingFile()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->expectException(\UserFrosting\Support\Exception\FileNotFoundException::class);
        $sprinkleManager->initFromSchema('foo.json');
    }

    /**
     * @depends testConstructor
     * @param  SprinkleManager $sprinkleManager
     * @return SprinkleManager
     */
    public function testGetSprinkles(SprinkleManager $sprinkleManager)
    {
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles.json');
        $sprinkles = $sprinkleManager->getSprinkles();
        $this->assertEquals([
            'foo'  => null,
            'bar'  => null,
            'test' => new \UserFrosting\Sprinkle\Test\Test(),
        ], $sprinkles);

        return $sprinkleManager;
    }

    /**
     * @depends testGetSprinkles
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinkleNames(SprinkleManager $sprinkleManager)
    {
        $sprinkles = $sprinkleManager->getSprinkleNames();
        $this->assertSame(['foo', 'bar', 'test'], $sprinkles);
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinkleNames
     * @param string          $sprinkleName
     * @param bool            $isAvailable
     * @param SprinkleManager $sprinkleManager
     * @testWith        ["bar", true]
     *                  ["test", true]
     *                  ["foo", true]
     *                  ["fOo", true]
     *                  ["foO", true]
     *                  ["Foo", true]
     *                  ["FOO", true]
     *                  ["fo", false]
     *                  ["o", false]
     *                  ["oo", false]
     *                  ["f0o", false]
     *                  ["foofoo", false]
     *                  ["1foo1", false]
     *                  ["barfoo", false]
     *                  ["blah", false]
     */
    public function testIsAvailable($sprinkleName, $isAvailable, SprinkleManager $sprinkleManager)
    {
        $this->assertSame($isAvailable, $sprinkleManager->isAvailable($sprinkleName));
    }

    /**
     * @depends testGetSprinkles
     * @param string          $sprinkleName
     * @param SprinkleManager $sprinkleManager
     * @testWith        ["foo"]
     *                  ["bar"]
     *                  ["test"]
     */
    public function testGetSprinklePath($sprinkleName, SprinkleManager $sprinkleManager)
    {
        $basePath = 'app/tests/Unit/data/';
        $sprinkleManager->setSprinklesPath($basePath);
        $this->assertSame($basePath . $sprinkleName, $sprinkleManager->getSprinklePath($sprinkleName));
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWherePathDoesntExist(SprinkleManager $sprinkleManager)
    {
        $basePath = 'app/tests/Unit/foo/';
        $sprinkleManager->setSprinklesPath($basePath);

        $this->expectException(\UserFrosting\Support\Exception\FileNotFoundException::class);
        $sprinkleManager->getSprinklePath('foo');
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWhereSprinkleDoesntExist(SprinkleManager $sprinkleManager)
    {
        $this->expectException(\UserFrosting\Support\Exception\FileNotFoundException::class);
        $sprinkleManager->getSprinklePath('blah');
    }

    /**
     * @depends testGetSprinkles
     * @param SprinkleManager $sprinkleManager
     */
    public function testRegisterAllServices(SprinkleManager $sprinkleManager)
    {
        // Set Expectations for test sprinkle ServiceProvider
        // @see https://stackoverflow.com/a/13390001/445757
        $this->getMockBuilder('nonexistant')
        ->setMockClassName('foo')
        ->setMethods(['register'])
        ->getMock();
        class_alias('foo', 'UserFrosting\Sprinkle\Test\ServicesProvider\ServicesProvider');

        $this->assertNull($sprinkleManager->registerAllServices());
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testAddResources(SprinkleManager $sprinkleManager)
    {
        $basePath = 'app/tests/Unit/data/';
        $sprinkleManager->setSprinklesPath($basePath);

        $this->assertNull($sprinkleManager->addResources());
    }

    /**
     * This will work, as long as it contains valid json
     *
     * @depends testConstructor
     * @depends testGetSprinkles
     */
    public function testLoadSprinkleWithTxtFile()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles.txt');
        $this->assertCount(3, $sprinkleManager->getSprinkles());
    }

    /**
     * @depends testConstructor
     */
    public function testLoadSprinkleWithBadJson()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->expectException(\UserFrosting\Support\Exception\JsonException::class);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles-bad.json');
    }

    /**
     * @depends testConstructor
     * @depends testIsAvailable
     */
    public function testLoadSprinkleWithDuplicateSprinkles()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles-duplicate.json');
        $this->assertEquals([
            'foo'  => null,
            'FOO'  => null,
            'bar'  => null,
        ], $sprinkleManager->getSprinkles());

        $this->assertTrue($sprinkleManager->isAvailable('Foo'));
    }
}

class eventDispatcherStub
{
    public function addSubscriber()
    {
    }
}

class ResourceLocatorStub
{
    public function registerLocation()
    {
    }
}
