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
use UserFrosting\Session\Session;

use UserFrosting\Sprinkle\Core\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Alert\SessionAlertStream;
use UserFrosting\I18n\Translator;

class SessionAlertStreamTest extends TestCase
{
    protected $key = 'alerts';

    protected $session_id = 'foo123';

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testConstructor()
    {
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);
        $stream = new SessionAlertStream($this->key, $translator, $session);

        $this->assertInstanceOf(AlertStream::class, $stream);
        $this->assertInstanceOf(SessionAlertStream::class, $stream);
    }

    /**
     * @depends testConstructor
     */
    public function testSetTranslator()
    {
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);
        $stream = new SessionAlertStream($this->key, $translator, $session);

        $this->assertSame($translator, $stream->translator());

        $translator2 = m::mock(Translator::class);
        $this->assertNotSame($translator, $translator2);
        $this->assertInstanceOf(SessionAlertStream::class, $stream->setTranslator($translator2));
        $this->assertSame($translator2, $stream->translator());
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessage()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $session->shouldReceive('get')->with($this->key)->once()->andReturn(false);
        $session->shouldReceive('set')->with($this->key, [$message])->once()->andReturn(null);

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $this->assertInstanceOf(SessionAlertStream::class, $stream->addMessage('success', 'foo'));
    }

    /**
     * @depends testAddMessage
     */
    public function testAddMessageWithExistingkeyNotEmpty()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $session->shouldReceive('get')->with($this->key)->once()->andReturn([$message]);
        $session->shouldReceive('set')->with($this->key, [$message, $message])->once()->andReturn(null);

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $this->assertInstanceOf(SessionAlertStream::class, $stream->addMessage('success', 'foo'));
    }

    /**
     * @depends testConstructor
     */
    public function testResetMessageStream()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);

        // Set expectations
        $session->shouldReceive('set')->with($this->key, [])->once()->andReturn(true);

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $this->assertNull($stream->resetMessageStream());
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessageTranslatedWithNoTranslator()
    {
        $session = m::mock(Session::class);
        $stream = new SessionAlertStream($this->key, null, $session);

        $this->expectException(\RuntimeException::class);
        $stream->addMessageTranslated('success', 'foo', []);
    }

    /**
     * @depends testConstructor
     */
    public function testAddMessageTranslated()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);

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
        $session->shouldReceive('get')->with($this->key)->once()->andReturn(false);
        $session->shouldReceive('set')->with($this->key, [$message])->once()->andReturn(null);

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $this->assertInstanceOf(SessionAlertStream::class, $stream->addMessageTranslated('success', $key, $placeholder));
    }

    /**
     * @depends testResetMessageStream
     */
    public function testGetAndClearMessages()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);

        // Set expectations
        $message = [
            'type'    => 'success',
            'message' => 'foo',
        ];
        $session->shouldReceive('get')->with($this->key)->once()->andReturn([$message]);
        $session->shouldReceive('set')->with($this->key, [])->once()->andReturn(true);

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $this->assertSame([$message], $stream->getAndClearMessages());
    }

    /**
     * @depends testAddMessage
     */
    public function testAddValidationErrors()
    {
        // Build Mock
        $translator = m::mock(Translator::class);
        $session = m::mock(Session::class);
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
        $session->shouldReceive('get')->with($this->key)->andReturn(false, [$message1], [$message1, $message2]); // Save 1, Save 2, Display both
        $session->shouldReceive('set')->with($this->key, [$message1])->andReturn(null); // Save 1
        $session->shouldReceive('set')->with($this->key, [$message1, $message2])->andReturn(null); // Save 2

        // Process
        $stream = new SessionAlertStream($this->key, $translator, $session);
        $stream->addValidationErrors($validator);
        $this->assertSame([$message1, $message2], $stream->messages());
    }
}
