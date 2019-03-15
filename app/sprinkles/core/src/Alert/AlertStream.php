<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Alert;

use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\I18n\MessageTranslator;

/**
 * AlertStream Class
 *
 * Implements an alert stream for use between HTTP requests, with i18n support via the MessageTranslator class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#messages
 */
abstract class AlertStream
{
    /**
     * @var string
     */
    protected $messagesKey;

    /**
     * @var \UserFrosting\I18n\MessageTranslator|null
     */
    protected $messageTranslator = null;

    /**
     * Create a new message stream.
     *
     * @param string                                    $messagesKey
     * @param \UserFrosting\I18n\MessageTranslator|null $translator
     */
    public function __construct($messagesKey, MessageTranslator $translator = null)
    {
        $this->messagesKey = $messagesKey;
        $this->setTranslator($translator);
    }

    /**
     * Set the translator to be used for all message streams.  Must be done before `addMessageTranslated` can be used.
     *
     * @param  \UserFrosting\I18n\MessageTranslator $translator A MessageTranslator to be used to translate messages when added via `addMessageTranslated`.
     * @return self
     */
    public function setTranslator(MessageTranslator $translator)
    {
        $this->messageTranslator = $translator;

        return $this;
    }

    /**
     * Adds a raw text message to the cache message stream.
     *
     * @param  string $type    The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param  string $message The message to be added to the message stream.
     * @return self   this MessageStream object.
     */
    public function addMessage($type, $message)
    {
        $messages = $this->messages();
        $messages[] = [
            'type'    => $type,
            'message' => $message
        ];
        $this->saveMessages($messages);

        return $this;
    }

    /**
     * Adds a text message to the cache message stream, translated into the currently selected language.
     *
     * @param  string            $type         The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param  string            $messageId    The message id for the message to be added to the message stream.
     * @param  array[string]     $placeholders An optional hash of placeholder names => placeholder values to substitute into the translated message.
     * @throws \RuntimeException
     * @return self              this MessageStream object.
     */
    public function addMessageTranslated($type, $messageId, $placeholders = [])
    {
        if (!$this->messageTranslator) {
            throw new \RuntimeException('No translator has been set!  Please call MessageStream::setTranslator first.');
        }

        $message = $this->messageTranslator->translate($messageId, $placeholders);

        return $this->addMessage($type, $message);
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

    /**
     * Add error messages from a ServerSideValidator object to the message stream.
     *
     * @param ServerSideValidator $validator
     */
    public function addValidationErrors(ServerSideValidator $validator)
    {
        foreach ($validator->errors() as $idx => $field) {
            foreach ($field as $eidx => $error) {
                $this->addMessage('danger', $error);
            }
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
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    abstract public function messages();

    /**
     * Clear all messages from this message stream.
     */
    abstract public function resetMessageStream();

    /**
     * Save messages to the stream
     *
     * @param string $message
     */
    abstract protected function saveMessages($message);
}
