<?php

/** Contains methods for validating user/group permissions.  Every method of this class MUST be static and return a boolean value. */
class AccessCondition {

    /**
    * Unconditionally grant permission - use carefully!
    */
    static function always(){
        return true;
    
    }

    // Check if the specified values are equal to one another.
    static function equals($val1, $val2){
        return ($val1 === $val2);
    }    

    // Check if all keys of the array $needle are present in the values of $haystack
    static function subset_keys($needle, $haystack){
        return count($needle) == count(array_intersect(array_keys($needle), $haystack));
    }

    // Check if all values in the array $needle are present in the values of $haystack
    static function subset($needle, $haystack){
        return count($needle) == count(array_intersect($needle, $haystack));
    }
    
    // Check if the specified value $needle is in the values of $haystack
    static function in($needle, $haystack){
        return in_array($needle, $haystack);
    } 
}
