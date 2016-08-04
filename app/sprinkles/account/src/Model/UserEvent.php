<?php

namespace UserFrosting\Sprinkle\Account\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * UserEvent Class
 *
 * Represents a single user event at a specified point in time.
 *
 */
class UserEvent extends UFModel
{    
    /**
     * @var string The name of the table for the current model.
     */ 
    protected $table = "user_event";
    
    protected $fillable = [
        "user_id",
        "event_type",
        "occurred_at",
        "description"
    ];
    
    /**
     * Add clauses to select the most recent event of each type for each user, to the query.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMostRecentEvents($query){
        return $query->select('user_id', 'event_type', Capsule::raw('MAX(occurred_at) as occurred_at'))
        ->groupBy('user_id')
        ->groupBy('event_type');
    }

    /**
     * Add clauses to select the most recent event of a given type for each user, to the query.
     *
     * @param string $type The type of event, matching the `event_type` field in the user_event table.
     * @return \Illuminate\Database\Query\Builder
     */    
    public function scopeMostRecentEventsByType($query, $type){
        return $query->select('user_id', Capsule::raw('MAX(occurred_at) as occurred_at'))
        ->where('event_type', $type)
        ->groupBy('user_id');
    }  
}
