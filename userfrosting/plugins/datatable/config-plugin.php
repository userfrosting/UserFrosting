<?php

namespace datatable;

/* This class is responsible for retrieving Group object(s) from the database, checking for existence, etc. */
//die("Loading datatable");
class datatable  {
    protected static $_columns;     // A list of the allowed columns for this type of DB object. Must be set in the child concrete class.  DO NOT USE `id` as a column!
    protected static $_table;       // The name of the table whose rows this class represents. Must be set in the child concrete class.    
    
    public static function init(){

//echo("This is the plugin config script");
        $app->schema->registerJS("dashboard", "http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0");    
    }
    
public function echobr($par_str) {
    echo("<br>$par_str<br>");
}

public function echoarr($par_arr, $par_comment = 'none') {
    if ($par_comment != 'none')
        echobr($par_comment);
    echo "<pre>";
    print_r($par_arr);
    echo "</pre>";
}
    
}
