<?php

namespace Fortress;

/**
 * MessageStream Class
 *
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
class MessageStream {

    /**
     * @var array
     */
    protected $_messages = [];
    
    /**
     * @var MessageTranslator
     */    
    protected static $_message_translator = null;

    /** Create a new message stream.
     *
     */
    public function __construct(){

    }

    /**
     * Set the translator to be used for all message streams.  Must be done before `addMessageTranslated` can be used.
     *
     * @param MessageTranslator $translator A MessageTranslator to be used to translate messages when added via `addMessageTranslated`.
     * @return MessageStream this MessageStream object. 
     */    
    public static function setTranslator($translator){
        static::$_message_translator = $translator;
    }
    
    /**
     * Adds a raw text message to the session message stream.
     *
     * @param string $type The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param string $message The message to be added to the message stream.
     * @return MessageStream this MessageStream object. 
     */
    public function addMessage($type, $message){
        $alert = [
            "type" => $type,
            "message" => $message
        ];
        $this->_messages[] = $alert;
        return $this;
    }

    /**
     * Adds a text message to the session message stream, translated into the currently selected language.
     *
     * @param string $type The type of message, indicating how it will be styled when outputted.  Should be set to "success", "danger", "warning", or "info".
     * @param string $message The message id for the message to be added to the message stream.
     * @param array $placeholders An optional hash of placeholder names => placeholder values to substitute into the translated message.
     * @return MessageStream this MessageStream object.
     */
    public function addMessageTranslated($type, $message_id, $placeholders = []){
        if (!static::$_message_translator){
            throw new \Exception("No translator has been set!  Please call MessageStream::setTranslator first.");
        }
        $message = static::$_message_translator->translate($message_id, $placeholders);
        return $this->addMessage($type, $message);
    }    
    
    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function messages(){
        return $this->_messages;
    }

    /**
     * Return the translator for this message stream.
     *
     * @return MessageTranslator The translator for this message stream.
     */
    public function translator(){
        if (!static::$_message_translator){
            throw new \Exception("No translator has been set!  Please call MessageStream::setTranslator first.");
        }    
        return static::$_message_translator;
    }
    
    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream(){
        $this->_messages = [];
    }
    
    /**
     * Get the messages and then clear the message stream.
     * This function does the same thing as `messages()`, except that it also clears all messages afterwards.
     * This is useful, because typically we don't want to view the same messages more than once.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function getAndClearMessages(){
        $messages = $this->_messages;
        $this->resetMessageStream();
        return $messages;
    }
}
