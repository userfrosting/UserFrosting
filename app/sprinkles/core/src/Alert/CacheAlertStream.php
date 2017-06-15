<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Alert;

/**
 * CacheAlertStream Class
 *
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 * Using the cache system to store the alerts. Note that the tags are added each time instead of the constructor
 * since the session_id can change when the user logs in or out
 *
 * @author Louis Charette
 */
class CacheAlertStream extends AlertStream
{
    /**
     * @var Illuminate\\Cache\\*Store Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $cache;

    /**
     * @var Illuminate\\Cache\\*Store Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $config;

    /**
     * Create a new message stream.
     */
    public function __construct($messagesKey, $translator = null, $cache, $config)
    {
        $this->cache = $cache;
        $this->config = $config;
        parent::__construct($messagesKey, $translator);
    }

    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function messages()
    {
        if ($this->cache->tags([$this->config['cache.prefix'], "_s".session_id()])->has($this->messagesKey))
        {
            return $this->cache->tags([$this->config['cache.prefix'], "_s".session_id()])->get($this->messagesKey);
        } else {
            return [];
        }
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->cache->tags([$this->config['cache.prefix'], "_s".session_id()])->forget($this->messagesKey);
    }

    /**
     * Save messages to the stream
     */
    protected function saveMessages($messages)
    {
        $this->cache->tags([$this->config['cache.prefix'], "_s".session_id()])->forever($this->messagesKey, $messages);
    }
}
