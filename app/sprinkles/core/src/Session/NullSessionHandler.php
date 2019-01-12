<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Session;

use SessionHandlerInterface;

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
