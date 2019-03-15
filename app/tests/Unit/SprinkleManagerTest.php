<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Unit;

use Interop\Container\ContainerInterface;
use Mockery as m;
use UserFrosting\Tests\TestCase;
use UserFrosting\System\Sprinkle\SprinkleManager;

class SprinkleManagerTest extends TestCase
{
    /** @var ContainerInterface $fakeCi Our mocked CI used for testing */
    protected $fakeCi;

    public function setUp()
    {
        // We don't call parent function to cancel CI creation and get accurate test coverage
        // Run only this test for accurage coverage report on SprinkleManager
        $this->fakeCi = m::mock(ContainerInterface::class);
        $this->fakeCi->eventDispatcher = new eventDispatcherStub();
        $this->fakeCi->locator = new ResourceLocatorStub();

        // Setup test sprinkle mock class so it can be found by `class_exist`
        m::mock('UserFrosting\Sprinkle\Test\Test');
    }

    public function tearDown()
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
     * @param  SprinkleManager $sprinkleManager
     * @return SprinkleManager
     */
    public function testInitFromSchema(SprinkleManager $sprinkleManager)
    {
        $sprinkleManager->initFromSchema(__DIR__ . '/data/sprinkles.json');

        return $sprinkleManager;
    }

    /**
     * @depends testConstructor
     * @expectedException \UserFrosting\Support\Exception\FileNotFoundException
     */
    public function testLoadSprinkleWithNonExistingFile()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
        $sprinkleManager->initFromSchema('foo.json');
    }

    /**
     * @depends testInitFromSchema
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinkles(SprinkleManager $sprinkleManager)
    {
        $sprinkles = $sprinkleManager->getSprinkles();
        $this->assertEquals([
            'foo'  => null,
            'bar'  => null,
            'test' => new \UserFrosting\Sprinkle\Test\Test()
        ], $sprinkles);
    }

    /**
     * @depends testInitFromSchema
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinkleNames(SprinkleManager $sprinkleManager)
    {
        $sprinkles = $sprinkleManager->getSprinkleNames();
        $this->assertSame(['foo', 'bar', 'test'], $sprinkles);
    }

    /**
     * @depends testInitFromSchema
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
     * @depends testInitFromSchema
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
     * @depends testInitFromSchema
     * @depends testGetSprinklePath
     * @expectedException \UserFrosting\Support\Exception\FileNotFoundException
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWherePathDoesntExist(SprinkleManager $sprinkleManager)
    {
        $basePath = 'app/tests/Unit/foo/';
        $sprinkleManager->setSprinklesPath($basePath);
        $sprinkleManager->getSprinklePath('foo');
    }

    /**
     * @depends testInitFromSchema
     * @depends testGetSprinklePath
     * @expectedException \UserFrosting\Support\Exception\FileNotFoundException
     * @param SprinkleManager $sprinkleManager
     */
    public function testGetSprinklePathWhereSprinkleDoesntExist(SprinkleManager $sprinkleManager)
    {
        $sprinkleManager->getSprinklePath('blah');
    }

    /**
     * @depends testInitFromSchema
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

        $sprinkleManager->registerAllServices();
    }

    /**
     * @depends testInitFromSchema
     * @depends testGetSprinklePath
     * @param SprinkleManager $sprinkleManager
     */
    public function testAddResources(SprinkleManager $sprinkleManager)
    {
        $basePath = 'app/tests/Unit/data/';
        $sprinkleManager->setSprinklesPath($basePath);
        $sprinkleManager->addResources();
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
     * @expectedException \UserFrosting\Support\Exception\JsonException
     */
    public function testLoadSprinkleWithBadJson()
    {
        $sprinkleManager = new SprinkleManager($this->fakeCi);
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
