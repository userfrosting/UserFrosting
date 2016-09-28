<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Util Class
 *
 * Static utility functions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Util
{
    /**
     * Extracts specific fields from one associative array, and places them into another.
     */
    static public function extractFields(&$inputArray, $fieldArray, $remove = true)
    {
        $result = [];
        
        foreach ($fieldArray as $name) {
            if (array_key_exists($name, $inputArray)) {
                $result[$name] = $inputArray[$name];
                
                // Optionally remove value from input array
                if ($remove) {
                    unset($inputArray[$name]);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Extracts numeric portion of a string (for example, for normalizing phone numbers).
     */
    static public function extractDigits($str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }
    
    /**
     * Formats a phone number as a standard 7- or 10-digit string (xxx) xxx-xxxx
     */
    static public function formatPhoneNumber($phone)
    {
        $num = static::extractDigits($phone);
        
        $len = strlen($num);
        if($len == 7)
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        elseif ($len == 10)
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
         
        return $num;    
    }
}
