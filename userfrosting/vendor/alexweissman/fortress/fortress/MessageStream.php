<?php

namespace Fortress;

// Implements a message stream, with i18n support via gettext

class MessageStream {

    protected $_messages = [];

    public function __construct(){
    }

    /* Clear the message stream */
    public function resetMessageStream(){
        $this->_messages = [];
    }    
     
    // Add a session message to the session message stream
    public function addMessage($type, $message){
        $alert = [
            "type" => $type,
            "message" => $message
        ];
        $this->_messages[] = $alert;
    }

    // Add a session message to the session message stream, translating as necessary
    public function addMessageTranslated($type, $message_id, $placeholders = []){
        $message = MessageTranslator::translate($message_id, $placeholders);
        $this->addMessage($type, $message);
    }    
    
    // Return the array of messages
    public function messages(){
        return $this->_messages;
    }

    // Return the array of messages
    public function getAndClearMessages(){
        $messages = $this->_messages;
        $this->resetMessageStream();
        return $messages;
    }
}




?>
