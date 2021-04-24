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
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Exception\JsonException;
use UserFrosting\System\Sprinkle\Sprinkle;
use UserFrosting\System\Sprinkle\SprinkleClassException;
use UserFrosting\Tests\TestCase;
use UserFrosting\System\Sprinkle\SprinkleManager;

class SprinkleManagerTest extends TestCase
{
    /** @var ContainerInterface $fakeCi Our mocked CI used for testing */
    protected $fakeCi;

    /** @var string */
    protected $basePath = __DIR__ . '/data/';

    public function setUp(): void
    {
        // We don't call parent function to cancel CI creation and get accurate test coverage
        // Run only this test for accurage coverage report on SprinkleManager
        $this->fakeCi = m::mock(ContainerInterface::class);
        $this->fakeCi->eventDispatcher = new eventDispatcherStub();
        $this->fakeCi->locator = new ResourceLocatorStub();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return SprinkleManager
     */
    public function testConstructor(): SprinkleManager
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->assertInstanceOf(SprinkleManager::class, $sprinkleManager);

        return $sprinkleManager;
    }

    /**
     * @depends testConstructor
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSetSprinklesPath(SprinkleManager $sprinkleManager): void
    {
        $sprinkleManager->setSprinklesPath('/foo');
        $this->assertSame('/foo', $sprinkleManager->getSprinklesPath());
    }

    /**
     * @depends testConstructor
     */
    public function testLoadSprinkleWithNonExistingFile(): void
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->expectException(FileNotFoundException::class);
        $sprinkleManager->initFromSchema('foo.json');
    }

    /**
     * Will test the instanceof sprinkle check is done + that the right class
     * is gnerated with the exception message assertion
     *
     * @depends testConstructor
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklesWihoutMockClass(SprinkleManager $sprinkleManager): void
    {
        // Setup test sprinkle mock class so it can be found by `class_exist`
        class_alias(BlahSprinkleStub::class, 'UserFrosting\Sprinkle\Blah\Blah');

        $this->expectException(SprinkleClassException::class);
        $this->expectExceptionMessage("UserFrosting\Sprinkle\Blah\Blah must be an instance of UserFrosting\System\Sprinkle\Sprinkle");
        $sprinkleManager->bootSprinkle('blah');
    }

    /**
     * @depends testConstructor
     * @param  SprinkleManager $sprinkleManager
     * @return SprinkleManager
     */
    public function testGetSprinkles(SprinkleManager $sprinkleManager): SprinkleManager
    {
        // Setup test sprinkle class alias so it can be found by `class_exist`
        class_alias(TestSprinkleStub::class, 'UserFrosting\Sprinkle\Test\Test');

        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles.json');

        $sprinkles = $sprinkleManager->getSprinkles();
        $this->assertIsArray($sprinkles);
        $this->assertCount(3, $sprinkles);
        $this->assertNull($sprinkles['foo']);
        $this->assertNull($sprinkles['bar']);
        $this->assertInstanceOf(Sprinkle::class, $sprinkles['test']);

        return $sprinkleManager;
    }

    /**
     * @depends testGetSprinkles
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinkleNames(SprinkleManager $sprinkleManager): void
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
    public function testIsAvailable($sprinkleName, $isAvailable, SprinkleManager $sprinkleManager): void
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
    public function testGetSprinklePath($sprinkleName, SprinkleManager $sprinkleManager): void
    {
        $sprinkleManager->setSprinklesPath($this->basePath);
        $this->assertSame($this->basePath . $sprinkleName, $sprinkleManager->getSprinklePath($sprinkleName));
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWherePathDoesntExist(SprinkleManager $sprinkleManager): void
    {
        $sprinkleManager->setSprinklesPath(__DIR__ . '/foo/');

        $this->expectException(FileNotFoundException::class);
        $sprinkleManager->getSprinklePath('foo');
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWhereSprinkleDoesntExist(SprinkleManager $sprinkleManager): void
    {
        $this->expectException(FileNotFoundException::class);
        $sprinkleManager->getSprinklePath('blah');
    }

    /**
     * @depends testGetSprinkles
     * @param SprinkleManager $sprinkleManager
     */
    public function testRegisterAllServices(SprinkleManager $sprinkleManager): void
    {
        // Set Expectations for test sprinkle ServiceProvider
        // @see https://stackoverflow.com/a/13390001/445757
        $this->getMockBuilder('nonexistant')
        ->setMockClassName('FooService') // MockClassName doesn't accept namespace
        ->setMethods(['register'])
        ->getMock();
        class_alias('FooService', 'UserFrosting\Sprinkle\Test\ServicesProvider\ServicesProvider');

        $this->assertNull($sprinkleManager->registerAllServices());
    }

    /**
     * @depends testGetSprinkles
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testAddResources(SprinkleManager $sprinkleManager): void
    {
        $sprinkleManager->setSprinklesPath($this->basePath);

        $this->assertNull($sprinkleManager->addResources());
    }

    /**
     * This will work, as long as it contains valid json
     *
     * @depends testConstructor
     * @depends testGetSprinkles
     */
    public function testLoadSprinkleWithTxtFile(): void
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles.txt');
        $this->assertCount(3, $sprinkleManager->getSprinkles());
    }

    /**
     * @depends testConstructor
     */
    public function testLoadSprinkleWithBadJson(): void
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $this->expectException(JsonException::class);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles-bad.json');
    }

    /**
     * @depends testConstructor
     * @depends testIsAvailable
     */
    public function testLoadSprinkleWithDuplicateSprinkles(): void
    {
        $sprinkleManager = m::mock(SprinkleManager::class, [$this->fakeCi])->makePartial();

        // Make sure the sprinkles are not booted twice
        $sprinkleManager->shouldReceive('bootSprinkle')->with('foo')->once();
        $sprinkleManager->shouldReceive('bootSprinkle')->with('bar')->once();

        $sprinkleManager->setSprinklesPath($this->basePath);
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles-duplicate.json');
        $sprinkles = $sprinkleManager->getSprinkles();

        $this->assertEquals([
            'foo'  => null,
            'bar'  => null,
        ], $sprinkles);

        // Test isAvailable work with only the correct case.
        $this->assertTrue($sprinkleManager->isAvailable('foo'));
        $this->assertTrue($sprinkleManager->isAvailable('Foo'));
        $this->assertTrue($sprinkleManager->isAvailable('FOO'));

        // Test getSprinklePath get the right case
        $this->assertsame(__DIR__ . '/data/bar', $sprinkleManager->getSprinklePath('bar'));
        $this->assertsame(__DIR__ . '/data/foo', $sprinkleManager->getSprinklePath('foo'));
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

class TestSprinkleStub extends Sprinkle
{
}

class BlahSprinkleStub
{
}