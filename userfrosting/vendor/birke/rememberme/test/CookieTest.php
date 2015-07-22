<?php

class CookieTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultValues()
    {
        $cookie = new \Birke\Rememberme\Cookie();

        $this->assertEquals('', $cookie->getPath());
        $this->assertEquals('', $cookie->getDomain());
        $this->assertFalse($cookie->getSecure());
        $this->assertTrue($cookie->getHttpOnly());
    }

    public function testSetters()
    {
        $cookie = new \Birke\Rememberme\Cookie();

        $cookie->setPath('/test');
        $this->assertEquals('/test', $cookie->getPath());

        $cookie->setDomain('www.foo.com');
        $this->assertEquals('www.foo.com', $cookie->getDomain());

        $cookie->setSecure(true);
        $this->assertTrue($cookie->getSecure());

        $cookie->setHttpOnly(false);
        $this->assertFalse($cookie->getHttpOnly());
    }
}
