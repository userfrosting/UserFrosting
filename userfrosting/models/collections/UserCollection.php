<?php 

namespace UserFrosting;

/**
 * UserCollection Class
 *
 * Represents a collection of User objects, and provides additional means for loading events and filtering.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class UserCollection extends \Illuminate\Database\Eloquent\Collection {
    
    /**
     * Get a list of the most recent event of a given type (e.g., "sign_in") for each user.
     *
     * On success, this will set the event times for the specified event type as attributes of the User models.
     * Unfortunately, we can't store $recent_event_times directly in the collection, because it won't get copied in other operations.
     * So, it must be returned by this method instead.
     * @see https://github.com/laravel/framework/issues/10695
     * @param string $type The type of event, matching the `event_type` field in the user_event table.
     * @param string $field_name optional The attribute name to use for this event in the User model.  If not specified, defaults to last_{$type}_time.
     * @return array An array of datetime strings, keyed by the `id` of each User.
     */
    public function getRecentEvents($type, $field_name = null) {        
        if (!$field_name)
            $field_name = "last_" . $type . "_time";
        
        $recentEventsQuery = UserEvent::mostRecentEventsByType($type);
        $recent_events = $recentEventsQuery->get();
        
        $recent_event_times = [];
        
        // extract sign-in times
        foreach($recent_events as $event){
            $recent_event_times[$event['user_id']] = $event['occurred_at'];
        }        
        
        // Merge in recent event times, and set any missing values
        foreach ($this as $user){
            if (isset($recent_event_times[$user->id]))
                $user->{$field_name} = $recent_event_times[$user->id];
            else {
                $user->{$field_name} = 0;   // Should be an integer, so it can be properly parsed on client-side
                $recent_event_times[$user->id] = 0;
            }
        }
        
        return $recent_event_times;
    }
    
    /**
     * Filter this collection based on a recent event time.
     *
     * This method allows you to filter Users by recent event times.  For example, you may filter users by their last sign-in time,
     * last sign-up time, last activation request time, etc.
     * @param string $type The type of event, matching the `event_type` field in the user_event table.
     * @param array @event_times An array of datetimes, indexed by the Users' `id`s.  This is typically the output of getRecentEvents().
     * @param string $value The value to search for.  This filter does a simple stripos for searching.
     * @param string $format_zero optional The date format to use when searching zero-valued dates.
     * @param string $format optional The date format to use when searching non-zero dates.  By default lets you search weekday, month, day, year, etc.
     * @return UserCollection The modified UserCollection.
     */
    public function filterRecentEventTime($type, $event_times, $value, $format_zero = "Brand New!", $format = "l F j, Y g:i a"){
        $result = $this->filter(function ($item) use ($value, $event_times, $format, $format_zero){
            // Get the recent event time from the stored array.  If we try to get it from the object itself, it will requery!
            $item_id = $item->id;
            if (isset($event_times[$item_id])) {
                $stamp = strtotime($event_times[$item_id]);
                $last_event_time = (($stamp != 0) ? date($format, $stamp) : $format_zero);
                return (stripos($last_event_time, $value) !== false);
            } else {
                return false;
            }
        });
        return $result;
    }
    
    /**
     * Filter this collection based on a specified User field.
     *
     * This method allows you to filter Users by any User field, such as user_name, email, etc.
     * @param string $name The name of the User field to filter by.
     * @param string $value The value to search for.  This filter does a simple stripos for searching.
     * @return UserCollection The modified UserCollection.
     */    
    public function filterTextField($name, $value) {
        $result = $this->filter(function ($item) use ($name, $value){
            return (stripos($item->{$name}, $value) !== false);
        });
        return $result;
    }
}
