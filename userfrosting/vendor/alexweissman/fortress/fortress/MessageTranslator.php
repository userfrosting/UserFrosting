<?php

namespace Fortress;

class MessageTranslator {

    protected static $_translation_table = [];
    protected static $_default_table = [];
    
    public static function setTranslationTable($path){
        static::$_translation_table = include($path);
    }

    public static function setDefaultTable($path){
        static::$_default_table = include($path);
    }
    
    /* Translate the given message hook into the currently configured language, substituting any placeholders that appear in the translated string.
     * Fall back to the default language if no match is found in the current language table.
     * @param string $message_id The name of the message hook to translate.
     * @param array $placeholders An optional hash of placeholder names => placeholder values to substitute
     * @return string The translated message.
    */
    public static function translate($message_id, $placeholders = []){
        // Get the message, translated into the currently set language
        if (isset(static::$_translation_table[$message_id])){
            $message = static::$_translation_table[$message_id];
        } else if (isset(static::$_default_table[$message_id])){
            $message = static::$_default_table[$message_id];
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

?>
