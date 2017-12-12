<?php
    require_once '../app/vendor/autoload.php';

    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }

    echo "/*******************************/\n/* UserFrosting's Build Assets */\n/*******************************/\n";

    $failCheck = 0;

    // Install npm
    passthru("npm install", $failCheck);

    // Check npm install succeeded.
    if ($failCheck !== 0) {
        echo PHP_EOL.PHP_EOL . "ERROR :: npm install failed.";
        exit(1);
    }
    
    // Install vendor assets
    passthru("npm run uf-assets-install");

    // Check vendor asset installation succeeded.
    if ($failCheck !== 0) {
        echo PHP_EOL.PHP_EOL."ERROR :: Frontend asset installation failed.";
        exit(1);
    } else {
        echo PHP_EOL."Assets install looks successful !".PHP_EOL.PHP_EOL;
    }
