<?php

/** Contains methods for validating user/group permissions.  Every method of this class MUST be static and return a boolean value. */
class AccessCondition {

    /**
    * Unconditionally grant permission - use carefully!
    */
    static function always(){
        return true;
    
    }

    static function equals($val1, $val2){
        return ($val1 == $val2);
    }    

    // Check if all keys in $needle are present in $haystack
    static function subset($needle, $haystack){
        return count($needle) == count(array_intersect(array_keys($needle), $haystack));
    }

    static function hasPost($user_id, $post_id){
        return false;
    } 
}
