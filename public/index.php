<?php
    // This is the path to autoload.php, your site's gateway to the rest of the UF codebase!  Make sure that it is correct!
    $init_path = "../app/vendor/autoload.php";

    // This if-block just checks that the path for initialize.php is correct.  Remove this once you know what you're doing.
    if (!file_exists($init_path)){
        echo "<h2>We can't seem to find our way to autoload.php!  Please check the require_once statement at the top of index.php, and make sure it contains the correct path to autoload.php.</h2><br>";
    }

    require_once($init_path);

    // Load configuration.  You may specify a mode and/or site in this constructor.
    $config = new UserFrosting\Config();
    
    // Boot up UF
    $app = new UserFrosting\UserFrosting($config);
    $app->process();
