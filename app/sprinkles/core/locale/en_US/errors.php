<?php

/**
 * en_US
 *
 * US English message token translations for the error pages
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

return [
    "ERROR" => [
        "@TRANSLATION" => "Error",

        "TITLE" => "Disturbance in the Force",
        "DESCRIPTION" => "We've sensed a great disturbance in the Force.",
        "ENCOUNTERED" => "Uhhh...something happened.  We don't know what.",
        "DETAIL" => "Here's what we got",
        "RETURN" => 'Click <a href="{{url}}">here</a> to return to the front page.',

        "400" => [
            "TITLE" => "Error 400: Bad Request",
            "DESCRIPTION" => "It's probably not your fault.",
        ],

        "404" => [
            "TITLE" => "Error 404: Page not found",
            "DESCRIPTION" => "We can't seem to find what you're looking for.",
            "DETAIL" => "We tried to find your page...",
            "EXPLAIN" => "We could not find the page you were looking for.",
            "RETURN" => 'Either way, click <a href="{{url}}">here</a> to return to the front page.'
        ],

        "CONFIG" => [
            "TITLE" => "UserFrosting Configuration Issue!",
            "DESCRIPTION" => "Some UserFrosting configuration requirements have not been met.",
            "DETAIL" => "Something's not right here.",
            "RETURN" => 'Please fix the following errors, then <a href="{{url}}">reload</a>.'
        ]
    ]
];
