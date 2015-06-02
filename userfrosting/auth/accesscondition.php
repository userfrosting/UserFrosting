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
    
    // Check if the specified user (by user_id) is in a particular group
    static function in_group($user_id, $group_id){
        $user = \UserFrosting\UserLoader::fetch($user_id);
        $groups = $user->getGroups();
        return isset($groups[$group_id]);
    }
}
