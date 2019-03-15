<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * German message token translations for the 'core' sprinkle.
 *
 * @author X-Anonymous-Y
 * @author kevinrombach
 * @author splitt3r
 */
return [
    'ERROR' => [
        '@TRANSLATION'                  => 'Fehler',

        '400' => [
            'TITLE'                     => 'Fehler 400: Ungültige Anforderung',
            'DESCRIPTION'               => 'Die Anfrage-Nachricht war fehlerhaft aufgebaut.',
        ],

        '404' => [
            'TITLE'                     => 'Fehler 404: Seite nicht gefunden',
            'DESCRIPTION'               => 'Die angeforderte Ressource wurde nicht gefunden.',
            'DETAIL'                    => 'Wir haben versucht Ihre Seite zu finden ...',
            'EXPLAIN'                   => 'Die von Ihnen gesuchte Seite konnte nicht gefunden werden.',
            'RETURN'                    => "Klicken Sie <a href='{{url}}'>Hier</a>, um zur Startseite zurückzukehren."
        ],

        'CONFIG' => [
            'TITLE'                     => 'UserFrosting Konfigurationsproblem!',
            'DESCRIPTION'               => 'Einige UserFrosting-Konfigurationsanforderungen wurden nicht erfüllt.',
            'DETAIL'                    => 'Etwas stimmt hier nicht.',
            'RETURN'                    => "Bitte beheben Sie die folgenden Fehler dann laden Sie die <a href='{{url}}'>Website</a> neu."
        ],

        'DESCRIPTION'                   => 'Wir haben eine große Störung in der Macht erkannt.',
        'DETAIL'                        => 'Hier haben wir:',

        'ENCOUNTERED'                   => 'Uhhh ... etwas ist passiert. Wir wissen nicht was.',

        'MAIL'                        => 'Schwerwiegender Fehler beim Mailversand, wenden Sie sich an Ihren Serveradministrator. Wenn Sie der Administrator sind, überprüfen Sie bitte das UF-Mail-Protokoll.',

        'RETURN'                        => "Klicken Sie <a href='{{url}}'>Hier</a>, um zur Startseite zurückzukehren.",

        'SERVER'                        => 'Hoppla, sieht aus als hätte der Server möglicherweise gepatzt. Wenn Sie ein Administrator sind, überprüfen Sie bitte die PHP- oder UF-Fehlerprotokolle.',

        'TITLE'                         => 'Störung in der Kraft'
    ]
];
