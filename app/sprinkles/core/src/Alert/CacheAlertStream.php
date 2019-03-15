<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Alert;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Support\Repository\Repository;

/**
 * CacheAlertStream Class
 * Implements a message stream for use between HTTP requests, with i18n
 * support via the MessageTranslator class using the cache system to store
 * the alerts. Note that the tags are added each time instead of the
 * constructor since the session_id can change when the user logs in or out
 *
 * @author Louis Charette
 */
class CacheAlertStream extends AlertStream
{
    /**
     * @var Cache Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $cache;

    /**
     * @var Repository Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $config;

    /**
     * Create a new message stream.
     *
     * @param string                 $messagesKey Store the messages under this key
     * @param MessageTranslator|null $translator
     * @param Cache                  $cache
     * @param Repository             $config
     */
    public function __construct($messagesKey, MessageTranslator $translator = null, Cache $cache, Repository $config)
    {
        $this->cache = $cache;
        $this->config = $config;
        parent::__construct($messagesKey, $translator);
    }

    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing 'type' and 'message' fields.
     */
    public function messages()
    {
        if ($this->cache->tags('_s'.session_id())->has($this->messagesKey)) {
            return $this->cache->tags('_s'.session_id())->get($this->messagesKey) ?: [];
        } else {
            return [];
        }
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->cache->tags('_s'.session_id())->forget($this->messagesKey);
    }

    /**
     * Save messages to the stream
     *
     * @param string $messages The message
     */
    protected function saveMessages($messages)
    {
        $this->cache->tags('_s'.session_id())->forever($this->messagesKey, $messages);
    }
}
