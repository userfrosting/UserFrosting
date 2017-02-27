<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Alert;

/**
 * CacheAlertStream Class
 *
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 * Using the cache system to store the alerts
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#messages
 */
class CacheAlertStream extends AlertStream
{
    /**
     * @var Illuminate\\Cache\\*Store Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $cache;

    /**
     * Create a new message stream.
     */
    public function __construct($messagesKey, $translator = null, $cache)
    {
        $this->cache = $cache;
        parent::__construct($messagesKey, $translator);
    }

    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function messages()
    {
        if ($this->cache->has($this->messagesKey))
        {
            return $this->cache->get($this->messagesKey);
        } else {
            return [];
        }
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->cache->forget($this->messagesKey);
    }

    /**
     * Save messages to the stream
     */
    protected function saveMessages($messages)
    {
        $this->cache->forever($this->messagesKey, $messages);
    }
}
