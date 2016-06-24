<?php

/**
 * MessageTranslator Class
 *
 * Translate message ids to a message in a specified language. 
 *
 * @package   userfrosting/i18n
 * @link      https://github.com/userfrosting/i18n
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\I18n;

use UserFrosting\Support\Exception\FileNotFoundException;

class MessageTranslator {

    /**
     * @var array
     */
    protected $translation_table = [];
    
    /**
     * @var array
     */    
    protected $default_table = [];
    
    /**
     * Set the path to the file containing the translation table to be used.  A translation table is an associative array of message ids => translated messages.
     *
     * @param string $path The full path to the translation file.
     */
    public function setTranslationTable($path)
    {
        if (!(file_exists($path) && is_readable($path)))
            throw new FileNotFoundException("The language file '$path' could not be found or is not readable.");
            
        $this->translation_table = include($path);
    }

    /**
     * Set the path to the file containing a default translation table, to be used when a message id is missing from the regular translation table.
     *
     * @param string $path The full path to the default translation file.
     */    
    public function setDefaultTable($path)
    {
        if (!(file_exists($path) && is_readable($path)))
            throw new FileNotFoundException("The language file '$path' could not be found or is not readable.");
        
        $this->default_table = include($path);
    }
    
    /**
     * Translate the given message id into the currently configured language, substituting any placeholders that appear in the translated string.
     *
     * Fall back to the default language if no match is found in the current language table.     
     * @param string $message_id The id of the message id to translate.
     * @param array $placeholders[optional] An optional hash of placeholder names => placeholder values to substitute.
     * @return string The translated message.  
     */
    public function translate($message_id, $placeholders = [])
    {
        // Get the message, translated into the currently set language
        if (isset($this->translation_table[$message_id])){
            $message = $this->translation_table[$message_id];
        } else if (isset($this->default_table[$message_id])){
            $message = $this->default_table[$message_id];
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
