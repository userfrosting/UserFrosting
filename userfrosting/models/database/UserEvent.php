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
        return $query->where('event_type', $event_type)->groupBy('user_id')->max('occurred_at');
    }
    
    public function scopeMostRecentEvents($query){
        //return $query->where('event_type', 'sign_in')->orderBy('occurred_at', 'desc')->limit(1)->select('user_id', 'occurred_at');
        return $query->select('user_id', 'event_type', Capsule::raw('MAX(occurred_at) as occurred_at'))
        ->groupBy('user_id')
        ->groupBy('event_type');
        
    }

    public function scopeMostRecentEventSignIn($query){
        //return $query->where('event_type', 'sign_in')->orderBy('occurred_at', 'desc')->limit(1)->select('user_id', 'occurred_at');
        return $query->select('user_id', Capsule::raw('MAX(occurred_at) as last_sign_in_time'))
        ->where('event_type', 'sign_in')
        ->groupBy('user_id');
    }
        
}
