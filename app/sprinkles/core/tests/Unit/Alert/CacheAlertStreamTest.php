<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Alert;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Alert\CacheAlertStream;
use Illuminate\Cache\Repository as Cache;
use UserFrosting\I18n\MessageTranslator;

class CacheAlertStreamTest extends TestCase
{
    protected $key = 'alerts';

    protected $session_id = 'foo123';

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testConstructor()
    {
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);

        $this->assertInstanceOf(AlertStream::class, $stream);
        $this->assertInstanceOf(CacheAlertStream::class, $stream);
    }

    /**
     * @depends testConstructor
     */
    public function testSetTranslator()
    {
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);

        $this->assertSame($translator, $stream->translator());

        $translator2 = m::mock(MessageTranslator::class);
        $this->assertNotSame($translator, $translator2);
        $this->assertInstanceOf(CacheAlertStream::class, $stream->setTranslator($translator2));
        $this->assertSame($translator2, $stream->translator());
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessage()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $tags->shouldReceive('has')->with($this->key)->once()->andReturn(false);
        $tags->shouldReceive('forever')->with($this->key, [$message])->once()->andReturn(null);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertInstanceOf(CacheAlertStream::class, $stream->addMessage('success', 'foo'));
    }

    /**
     * @depends testAddMessage
     */
    public function testAddMessageWithExistingkey()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $tags->shouldReceive('has')->with($this->key)->once()->andReturn(true);
        $tags->shouldReceive('get')->with($this->key)->once()->andReturn(false);
        $tags->shouldReceive('forever')->with($this->key, [$message])->once()->andReturn(null);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertInstanceOf(CacheAlertStream::class, $stream->addMessage('success', 'foo'));
    }

    /**
     * @depends testAddMessageWithExistingkey
     */
    public function testAddMessageWithExistingkeyNotEmpty()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $tags->shouldReceive('has')->with($this->key)->once()->andReturn(true);
        $tags->shouldReceive('get')->with($this->key)->once()->andReturn([$message]);
        $tags->shouldReceive('forever')->with($this->key, [$message, $message])->once()->andReturn(null);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertInstanceOf(CacheAlertStream::class, $stream->addMessage('success', 'foo'));
    }

    /**
     * @depends testConstructor
     */
    public function testResetMessageStream()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        // Set expectations
        $tags->shouldReceive('forget')->with($this->key)->once()->andReturn(true);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertNull($stream->resetMessageStream());
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessageTranslatedWithNoTranslator()
    {
        $cache = m::mock(Cache::class);
        $stream = new CacheAlertStream($this->key, null, $cache, $this->session_id);

        $this->expectException(\RuntimeException::class);
        $stream->addMessageTranslated('success', 'foo', []);
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessageTranslated()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        //
        $key = 'FOO';
        $placeholder = ['key' => 'value'];
        $result = 'Bar';

        // Set expectations
        $translator->shouldReceive('translate')->with($key, $placeholder)->andReturn($result);
        $message = [
            'type'    => 'success',
            'message' => $result,
        ];
        $tags->shouldReceive('has')->with($this->key)->once()->andReturn(false);
        $tags->shouldReceive('forever')->with($this->key, [$message])->once()->andReturn(null);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertInstanceOf(CacheAlertStream::class, $stream->addMessageTranslated('success', $key, $placeholder));
    }

    /**
     * @depends testAddMessageWithExistingkey
     * @depends testResetMessageStream
     */
    public function testGetAndClearMessages()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $tags->shouldReceive('forget')->with($this->key)->once()->andReturn(true);
        $tags->shouldReceive('has')->with($this->key)->once()->andReturn(true);
        $tags->shouldReceive('get')->with($this->key)->once()->andReturn([$message]);
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $this->assertSame([$message], $stream->getAndClearMessages());
    }

    /**
     * @depends testAddMessage
     */
    public function testAddValidationErrors()
    {
        // Build Mock
        $translator = m::mock(MessageTranslator::class);
        $cache = m::mock(Cache::class);
        $tags = m::mock(\Illuminate\Cache\TaggedCache::class);
        $validator = m::mock(\UserFrosting\Fortress\ServerSideValidator::class);

        // Set expectations
        $data = [
            'name'  => ['Name is required'],
            'email' => ['Email should be a valid email address'],
        ];
        $validator->shouldReceive('errors')->once()->andReturn($data);

        $message1 = [
            'type'    => 'danger',
            'message' => 'Name is required',
        ];
        $message2 = [
            'type'    => 'danger',
            'message' => 'Email should be a valid email address',
        ];
        $tags->shouldReceive('has')->with($this->key)->andReturn(false, [$message1], [$message1, $message2]); // Save 1, Save 2, display both
        $tags->shouldReceive('get')->with($this->key)->andReturn([$message1], [$message1, $message2]); // Save 2, Display both
        $tags->shouldReceive('forever')->with($this->key, [$message1])->once()->andReturn(null); // Save 1
        $tags->shouldReceive('forever')->with($this->key, [$message1, $message2])->once()->andReturn(null); // Save 2
        $cache->shouldReceive('tags')->with("_s".$this->session_id)->andReturn($tags);

        // Process
        $stream = new CacheAlertStream($this->key, $translator, $cache, $this->session_id);
        $stream->addValidationErrors($validator);
        $this->assertSame([$message1, $message2], $stream->messages());
    }
}
