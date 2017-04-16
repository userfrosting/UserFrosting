<?php
    require_once '../app/vendor/autoload.php';

    if (!defined('STDIN')) {
        die('This program must be run from the command line.');
    }

    echo "/*******************************/\n/* UserFrosting's Build Assets */\n/*******************************/\n";

    // Install npm
    passthru("npm install --prefix ../build");

    // Install bower
    passthru("npm run uf-assets-install --prefix ../build");

    // Test it did worked
    $coreVendorFiles = glob(UserFrosting\APP_DIR . '/' . UserFrosting\SPRINKLES_DIR_NAME . "/core/assets/vendor/*");
    if (!$coreVendorFiles){
        echo PHP_EOL.PHP_EOL."ERROR :: NPM bundle failed. Directory '" . UserFrosting\APP_DIR . '/' . UserFrosting\SPRINKLES_DIR_NAME . "/core/assets/vendor/' is empty." .PHP_EOL;
        exit(1);
    } else {
        echo PHP_EOL."Assets install looks successful !";
        echo PHP_EOL.PHP_EOL;
    }
