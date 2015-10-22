<?php 

namespace UserFrosting;

class UserCollection extends \Illuminate\Database\Eloquent\Collection {

    protected $recent_event_times = [];

    public function loadRecentEvents($type, $field_name = null) {
        if (!$field_name)
            $field_name = "last_" . $type . "_time";
        
        $recentEventsQuery = UserEvent::mostRecentEvent($type);
        $sign_in_events = $recentEventsQuery->get();
        
        $this->recent_event_times[$type] = [];
        // extract sign-in times
        foreach($sign_in_events as $event){
            $this->recent_event_times[$type][$event['user_id']] = $event['last_event_time'];
        }        
        
        // Merge in recent event times, and set any missing values
        foreach ($this as $user){
            if (isset($this->recent_event_times[$type][$user->id]))
                $user->{$field_name} = $this->recent_event_times[$type][$user->id];
            else {
                $user->{$field_name} = "0";
                $this->recent_event_times[$type][$user->id] = "0";
            }
        }
        
        return $this;
    }
    
    public function filterRecentEventTime($type, $value, $format_zero = "Brand New!", $format = "l F j, Y g:i a"){
        $event_times = $this->recent_event_times[$type];
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
    
    public function getRecentEventTimes($type){
        return $this->recent_event_times[$type];
    }

}
