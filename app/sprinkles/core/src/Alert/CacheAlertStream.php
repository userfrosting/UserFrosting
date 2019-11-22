<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Alert;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\I18n\Translator;

/**
 * CacheAlertStream Class
 * Implements a message stream for use between HTTP requests, with i18n
 * support via the Translator class using the cache system to store
 * the alerts. Note that the tags are added each time instead of the
 * constructor since the session_id can change when the user logs in or out.
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
     * @var string Session id tied to the alert stream
     */
    protected $session_id;

    /**
     * Create a new message stream.
     *
     * @param string          $messagesKey Store the messages under this key
     * @param Translator|null $translator
     * @param Cache           $cache
     * @param string          $sessionId
     */
    public function __construct($messagesKey, Translator $translator = null, Cache $cache, $sessionId)
    {
        $this->cache = $cache;
        $this->session_id = $sessionId;
        parent::__construct($messagesKey, $translator);
    }

    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing 'type' and 'message' fields.
     */
    public function messages()
    {
        if ($this->getCache()->has($this->messagesKey)) {
            return $this->getCache()->get($this->messagesKey) ?: [];
        } else {
            return [];
        }
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->getCache()->forget($this->messagesKey);
    }

    /**
     * Save messages to the stream.
     *
     * @param array $messages The message
     */
    protected function saveMessages(array $messages)
    {
        $this->getCache()->forever($this->messagesKey, $messages);
    }

    /**
     * @return \Illuminate\Cache\TaggedCache
     */
    protected function getCache()
    {
        return $this->cache->tags('_s' . $this->session_id);
    }
}
