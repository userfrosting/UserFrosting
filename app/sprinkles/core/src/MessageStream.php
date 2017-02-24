<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core;

use UserFrosting\Fortress\ServerSideValidator;

/**
 * MessageStream Class
 *
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#messages
 */
class MessageStream
{
    /**
     * @var Illuminate\\Cache\\*Store Object We use the cache object so that added messages will automatically appear in the cache.
     */
    protected $cache;

    /**
     * @var string
     */
    protected $messagesKey;

    /**
     * @var UserFrosting\I18n\MessageTranslator|null
     */
    protected $messageTranslator = null;

    /**
     * Create a new message stream.
     */
    public function __construct($cache, $messagesKey, $translator = null)
    {
        $this->cache = $cache;
        $this->messagesKey = $messagesKey;

        $this->setTranslator($translator);
    }

    /**
     * Set the translator to be used for all message streams.  Must be done before `addMessageTranslated` can be used.
     *
     * @param UserFrosting\I18n\MessageTranslator $translator A MessageTranslator to be used to translate messages when added via `addMessageTranslated`.
     */
    public function setTranslator($translator)
    {
        $this->messageTranslator = $translator;
        return $this;
    }

    /**
     * Adds a raw text message to the cache message stream.
     *
     * @param string $type The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param string $message The message to be added to the message stream.
     * @return MessageStream this MessageStream object.
     */
    public function addMessage($type, $message)
    {
        $messages = $this->messages();
        $messages[] = array(
            "type" => $type,
            "message" => $message
        );
        $this->cache->forever($this->messagesKey, $messages);
        return $this;
    }

    /**
     * Adds a text message to the cache message stream, translated into the currently selected language.
     *
     * @param string $type The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param string $messageId The message id for the message to be added to the message stream.
     * @param array[string] $placeholders An optional hash of placeholder names => placeholder values to substitute into the translated message.
     * @return MessageStream this MessageStream object.
     */
    public function addMessageTranslated($type, $messageId, $placeholders = array())
    {
        if (!$this->messageTranslator){
            throw new \RuntimeException("No translator has been set!  Please call MessageStream::setTranslator first.");
        }

        $message = $this->messageTranslator->translate($messageId, $placeholders);
        return $this->addMessage($type, $message);
    }

    /**
     * Add error messages from a ServerSideValidator object to the message stream.
     *
     * @param ServerSideValidator $validator
     */
    public function addValidationErrors(ServerSideValidator $validator)
    {
        foreach ($validator->errors() as $idx => $field) {
            foreach($field as $eidx => $error) {
                $this->addMessage("danger", $error);
            }
        }
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
     * Return the translator for this message stream.
     *
     * @return MessageTranslator The translator for this message stream.
     */
    public function translator()
    {
        return $this->messageTranslator;
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->cache->forget($this->messagesKey);
    }

    /**
     * Get the messages and then clear the message stream.
     * This function does the same thing as `messages()`, except that it also clears all messages afterwards.
     * This is useful, because typically we don't want to view the same messages more than once.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function getAndClearMessages()
    {
        $messages = $this->messages();
        $this->resetMessageStream();
        return $messages;
    }
}
