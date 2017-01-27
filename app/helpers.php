<?php

function loadSprinkleSchema()
{
    $sprinklesFile = file_get_contents(UserFrosting\APP_DIR . '/' . UserFrosting\SPRINKLES_DIR_NAME . '/sprinkles.json');

    if ($sprinklesFile === false) {
        ob_clean();
        $title = "UserFrosting Application Error";
        $errorMessage = "Unable to start site. Contact owner.<br/><br/>" .
            "Version: UserFrosting ".UserFrosting\VERSION."<br/>Error: Unable to determine Sprinkle load order.";
        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $errorMessage
        );
        exit($output);
    }

    return json_decode($sprinklesFile);
}
