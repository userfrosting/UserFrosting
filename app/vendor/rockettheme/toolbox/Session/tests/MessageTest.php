<?php

use RocketTheme\Toolbox\Session\Message;

class SessionMessageTest extends PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $message = new Message;
        $this->assertTrue(true);
    }
}
