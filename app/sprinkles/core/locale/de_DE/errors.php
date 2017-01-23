<?php

/**
 * de_DE
 *
 * German message token translations for the error pages
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author @X-Anonymous-Y
 */

return [
    "ERROR" => [
        "@TRANSLATION"                  => "Fehler",

        "TITLE"                         => "Störung in der Kraft",
        "DESCRIPTION"                   => "Wir haben eine große Störung in der Macht erkannt.",
        "ENCOUNTERED"                   => "Uhhh ... etwas ist passiert. Wir wissen nicht was.",
        "DETAIL"                        => "Hier haben wir:",
        "RETURN"                        => "Klicken Sie <a href='{{url}}'>Hier</a>, um zur Startseite zurückzukehren.",

        "SERVER"                        => "Hoppla, sieht aus als hätte der Server möglicherweise gepatzt. Wenn Sie ein Administrator sind, überprüfen Sie bitte die PHP- oder UF-Fehlerprotokolle.",

        "400" => [
            "TITLE"                     => "Fehler 400: Ungültige Anforderung",
            "DESCRIPTION"               => "Die Anfrage-Nachricht war fehlerhaft aufgebaut.",
        ],

        "404" => [
            "TITLE"                     => "Fehler 404: Seite nicht gefunden",
            "DESCRIPTION"               => "Die angeforderte Ressource wurde nicht gefunden.",
            "DETAIL"                    => "Wir haben versucht Ihre Seite zu finden ...",
            "EXPLAIN"                   => "Die von Ihnen gesuchte Seite konnte nicht gefunden werden.",
            "RETURN"                    => "Klicken Sie <a href='{{url}}'>Hier</a>, um zur Startseite zurückzukehren."
        ],

        "CONFIG" => [
            "TITLE"                     => "UserFrosting Konfigurationsproblem!",
            "DESCRIPTION"               => "Einige UserFrosting-Konfigurationsanforderungen wurden nicht erfüllt.",
            "DETAIL"                    => "Etwas stimmt hier nicht.",
            "RETURN"                    => "Bitte beheben Sie die folgenden Fehler dann laden Sie die <a href='{{url}}'>Website</a> neu."
        ]
    ]
];