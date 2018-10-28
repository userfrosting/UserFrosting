<?php

namespace UserFrosting\Sprinkle\Core\Session;

use SessionHandlerInterface;

use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Temp class until we update to Laravel 5.5, so we can use theirs
 */
class NullSessionHandler implements SessionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        return true;
    }
}
