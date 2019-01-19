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
    * @param  SprinkleManager $sprinkleManager
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
        $this->assertSame([
            'foo'  => null,
            'bar'  => null,
            'test' => null
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
     *                  ["foo", true]
     *                  ["fOo", true]
     *                  ["foO", true]
     *                  ["Foo", true]
     *                  ["FOO", true]
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
     * @param bool            $path
     * @param SprinkleManager $sprinkleManager
     * @testWith        ["foo", "/foo/foo"]
     *                  ["bar", "/foo/bar"]
     *                  ["test", "/foo/test"]
     */
    public function testGetSprinklePath($sprinkleName, $path, SprinkleManager $sprinkleManager)
    {
        $sprinkleManager->setSprinklesPath('/foo/');
        $this->assertSame($path, $sprinkleManager->getSprinklePath($sprinkleName));
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
        $this->assertSame([
            'foo'  => null,
            'FOO'  => null,
            'bar'  => null,
            'test' => null
        ], $sprinkleManager->getSprinkles());

        $this->assertTrue($sprinkleManager->isAvailable('Foo'));
    }
}
