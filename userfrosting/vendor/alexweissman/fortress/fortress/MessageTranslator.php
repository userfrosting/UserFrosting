<?php

namespace Fortress;

/**
 * MessageTranslator Class
 *
 * Translate message ids to a message in a specified language. 
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
class MessageTranslator {

    /**
     * @var array
     */
    protected $_translation_table = [];
    
    /**
     * @var array
     */    
    protected $_default_table = [];
    
    /**
     * Set the path to the file containing the translation table to be used.  A translation table is an associative array of message ids => translated messages.
     *
     * @param string $path The full path to the translation file.
     */
    public function setTranslationTable($path){
        $this->_translation_table = include($path);
    }

    /**
     * Set the path to the file containing a default translation table, to be used when a message id is missing from the regular translation table.
     *
     * @param string $path The full path to the default translation file.
     */    
    public function setDefaultTable($path){
        $this->_default_table = include($path);
    }
    
    /**
     * Translate the given message id into the currently configured language, substituting any placeholders that appear in the translated string.
     *
     * Fall back to the default language if no match is found in the current language table.     
     * @param string $message_id The id of the message id to translate.
     * @param array $placeholders[optional] An optional hash of placeholder names => placeholder values to substitute.
     * @return string The translated message.  
     */
    public function translate($message_id, $placeholders = []){
        // Get the message, translated into the currently set language
        if (isset($this->_translation_table[$message_id])){
            $message = $this->_translation_table[$message_id];
        } else if (isset($this->_default_table[$message_id])){
            $message = $this->_default_table[$message_id];
        } else {
            $message = $message_id;    
        }
        
        // Interpolate placeholders
        foreach ($placeholders as $name => $value){
            if (gettype($value) != "array" && gettype($value) != "object") {
                $find = '{{' . trim($name) . '}}';
                $message = str_replace($find, $value, $message);
            }
        }
      
        return $message;
    }
}
