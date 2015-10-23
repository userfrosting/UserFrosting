<?php 

namespace UserFrosting;

class UserCollection extends \Illuminate\Database\Eloquent\Collection {
    
    // Unfortunately, we can't store $recent_event_times directly in the collection, because it won't get copied in other operations.
    // See https://github.com/laravel/framework/issues/10695
    public function getRecentEvents($type, $field_name = null) {        
        if (!$field_name)
            $field_name = "last_" . $type . "_time";
        
        $recentEventsQuery = UserEvent::mostRecentEvent($type);
        $recent_events = $recentEventsQuery->get();
        
        $recent_event_times = [];
        
        // extract sign-in times
        foreach($recent_events as $event){
            $recent_event_times[$event['user_id']] = $event['last_event_time'];
        }        
        
        // Merge in recent event times, and set any missing values
        foreach ($this as $user){
            if (isset($recent_event_times[$user->id]))
                $user->{$field_name} = $recent_event_times[$user->id];
            else {
                $user->{$field_name} = "0";
                $recent_event_times[$user->id] = "0";
            }
        }
        
        return $recent_event_times;
    }
    
    
    public function filterRecentEventTime($type, $event_times, $value, $format_zero = "Brand New!", $format = "l F j, Y g:i a"){
        $result = $this->filter(function ($item) use ($value, $event_times, $format, $format_zero){
            // Get the recent event time from the stored array.  If we try to get it from the object itself, it will requery!
            $item_id = $item->id;
            if (isset($event_times[$item_id])) {
                $stamp = strtotime($event_times[$item_id]);
                $last_event_time = (($stamp != "0") ? date($format, $stamp) : $format_zero);
                return (stripos($last_event_time, $value) !== false);
            } else {
                return false;
            }
        });
        return $result;
    }
    
    public function filterTextField($name, $value) {
        $result = $this->filter(function ($item) use ($name, $value){
            return (stripos($item->{$name}, $value) !== false);
        });
        return $result;
    }
}
