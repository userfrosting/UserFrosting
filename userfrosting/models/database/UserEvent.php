<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * UserEvent Class
 *
 * Represents a single user event at a specified point in time.
 *
 * @see DatabaseInterface
 */
class UserEvent extends UFModel {    
    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "user_event";
    
    public function scopeMostRecent($query, $event_type){
        return $query->where('event_type', $event_type)->max('occurred_at');
    }
    
    public function scopeLastSignInTimes($query){
        return $query->where('event_type', 'sign_in')->orderBy('occurred_at', 'desc')->limit(1)->select('user_id', 'occurred_at');
    }
    
}
