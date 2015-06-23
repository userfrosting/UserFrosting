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
    protected $_message_translator;

    /** Create a new message stream.
     *
     * @param MessageTranslator $message_translator A MessageTranslator to be used to translate messages when added via `addMessageTranslated`.
     * If not specified, one will be automatically created.
     */
    public function __construct($message_translator = null){
        if ($message_translator){
            $this->_message_translator = $message_translator;
        } else {
            $this->_message_translator = new MessageTranslator();
        }
    }

    /**
     * Set the path(s) to the file(s) containing the translation table(s) to be used.  A translation table is an associative array of message ids => translated messages.
     *
     * @param string $path The full path to the regular translation file.
     * @param string $path_default The path to a backup translation file, when a message id cannot be found in the regular translation table.
     * @return MessageStream this MessageStream object. 
     */    
    public function setTranslationTable($path, $path_default = null){
        $this->_message_translator->setTranslationTable($path);
        if ($path_default){
            $this->_message_translator->setDefaultTable($path_default);
        }
        return $this;
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
        $message = $this->_message_translator->translate($message_id, $placeholders);
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
        return $this->_message_translator;
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
