<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UserFrosting\Sprinkle\Core\Filesystem\FilesystemManager;
use UserFrosting\Sprinkle\Core\Facades\Storage;
use UserFrosting\Tests\TestCase;

/**
 * FilesystemTest class.
 * Tests a basic filesystem.
 */
class FilesystemTest extends TestCase
{
    /** @var string Testing storage path */
    private $testDir;

    /** @var string Test disk name */
    private $testDisk = 'testing';

    /**
     * Setup TestDatabase
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        $this->testDir = $this->ci->config["filesystems.disks.{$this->testDisk}.root"];
    }

    /**
     * Test the service and FilesystemManager
     */
    public function testService()
    {
        // Force this test to use the testing disk
        $this->ci->config['filesystems.default'] = $this->testDisk;

        // Filesystem service will return an instance of FilesystemManger
        $filesystem = $this->ci->filesystem;
        $this->assertInstanceOf(FilesystemManager::class, $filesystem);

        // Main aspect of our FilesystemManager is to adapt our config structure
        // to Laravel class we'll make sure here the forced config actually works
        $this->assertEquals($this->testDisk, $filesystem->getDefaultDriver());

        // The disk won't return a Manager, but an Adapter.
        $disk = $filesystem->disk($this->testDisk);
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        return $disk;
    }

    /**
     * @depends testService
     */
    public function testFacade()
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk($this->testDisk));
    }

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     */
    public function testAdapter(FilesystemAdapter $files)
    {
        // Test basic "put"
        $this->assertTrue($files->put('file.txt', 'Something inside'));
        $this->assertStringEqualsFile($this->testDir . '/file.txt', 'Something inside');

        // Test "exist" & "get"
        // We'll assume Laravel test covers the rest ;)
        $this->assertTrue($files->exists('file.txt'));
        $this->assertEquals('Something inside', $files->get('file.txt'));

        // We'll delete the test file now
        $this->assertTrue($files->delete('file.txt'));
        $this->assertFileNotExists($this->testDir.'/file.txt');
    }

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     * NOTE : The `download` method was introduced in Laravel 5.5.
     * We'll need to enable this once we can upgrade to newer version of Laravel
     */
    /*public function testDownload(FilesystemAdapter $files)
    {
        // We'll test the file download feature
        $response = $files->download('file.txt', 'hello.txt');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        // $this->assertEquals('attachment; filename="hello.txt"', $response->headers->get('content-disposition'));
    }*/

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     */
    public function testUrl(FilesystemAdapter $files)
    {
        // Test the URL
        $this->assertTrue($files->put('file.txt', 'Blah!'));
        $url = $files->url('file.txt');
        $this->assertEquals('files/testing/file.txt', $url);
        $this->assertTrue($files->delete('file.txt'));
        $this->assertFileNotExists($this->testDir.'/file.txt');
    }

    /**
     * Test to make sure we can still add custom adapter
     */
    public function testNonExistingAdapter()
    {
        $filesystemManager = $this->ci->filesystem;

        // InvalidArgumentException
        $this->expectException('InvalidArgumentException');
        $filesystemManager->disk('testingDriver');
    }

    /**
     * @depends testNonExistingAdapter
     */
    public function testAddingAdapter()
    {
        $filesystemManager = $this->ci->filesystem;

        $filesystemManager->extend('localTest', function ($configService, $config) {
            return new Filesystem(new LocalAdapter($config['root']));
        });

        $disk = $filesystemManager->disk('testingDriver');
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        // Make sure the path was set correctly
        $this->assertEquals(\UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testingDriver' . \UserFrosting\DS, $disk->path(''));
    }
}
