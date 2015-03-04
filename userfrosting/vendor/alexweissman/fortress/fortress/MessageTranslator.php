<?php

namespace Fortress;

class MessageTranslator {

    protected static $_translation_table = [];

    public static function setTranslationTable($path){
        static::$_translation_table = include($path);
    }


    public static function translate($message_id, $placeholders = []){
        // Get the message, translated into the currently set language
        if (isset(static::$_translation_table[$message_id])){
            $message = static::$_translation_table[$message_id];
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
