<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * German message token translations for the 'account' sprinkle.
 *
 * @author X-Anonymous-Y
 * @author kevinrombach
 * @author splitt3r
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION'                  => 'Konto',

        'ACCESS_DENIED'                 => 'Hmm, sieht aus als hätten Sie keine Berechtigung, um dies zu tun.',

        'DISABLED'                      => 'Dieses Konto wurde deaktiviert. Bitte Kontaktieren Sie uns für weitere Informationen.',

        'EMAIL_UPDATED'                 => 'E-Mail-Adresse aktualisiert.',

        'INVALID'                       => 'Dieses Konto existiert nicht. Es wurde möglicherweise gelöscht. Bitte kontaktieren Sie uns für weitere Informationen.',

        'MASTER_NOT_EXISTS'             => 'Sie können kein neues Konto anlegen solange kein Root-Konto angelegt wurde!',
        'MY'                            => 'Mein Konto',

        'SESSION_COMPROMISED' => [
            '@TRANSLATION'              => 'Ihre Sitzung wurde beeinträchtigt. Sie sollten sich auf allen Geräten abmelden, sich dann wieder anmelden und sicherstellen, dass Ihre Daten nicht manipuliert wurden.',
            'TITLE'                     => 'Ihr Konto wurde möglicherweise beeinträchtigt',
            'TEXT'                      => 'Möglicherweise ist es jemandem gelungen, Ihren Zugang zu dieser Seite zu übernehmen. Aus Sicherheitsgründen wurden Sie überall abgemeldet. Bitte <a href="{{url}}">melden Sie sich neu an</a> und untersuchen Sie das Konto nach verdächtigen Aktivitäten. Außerdem sollten Sie Ihr Passwort ändern.'
        ],
        'SESSION_EXPIRED'               => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',

        'SETTINGS' => [
            '@TRANSLATION'              => 'Kontoeinstellungen',
            'DESCRIPTION'               => 'Aktualisieren Sie Ihre Kontoeinstellungen, einschließlich E-Mail, Name und Passwort.',
            'UPDATED'                   => 'Kontoeinstellungen aktualisiert'
        ],

        'TOOLS'                         => 'Konto-Werkzeuge',

        'UNVERIFIED'                    => 'Ihr Konto wurde noch nicht bestätigt. Überprüfen Sie Ihr E-Mails/Spam-Ordner für die Konto-Aktivierungsanleitung.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'             => 'Wir haben einen neuen Bestätigungslink an {{email}} gesendet. Überprüfen Sie Ihr E-Mail/Spam-Ordner oder versuchen Sie es später noch einmal.',
            'RESEND'                    => 'Bestätigungsmail erneut senden',
            'COMPLETE'                  => 'Sie haben Ihr Konto erfolgreich Verifiziert. Sie können sich jetzt anmelden.',
            'EMAIL'                     => 'Bitte geben Sie die E-Mail-Adresse ein, mit der Sie sich registriert haben, Überprüfen Sie Ihr E-Mails/Spam-Ordner für die Bestätigungs-E-Mail.',
            'PAGE'                      => 'Senden Sie die Bestätigungs-E-Mail erneut für Ihr neues Konto.',
            'SEND'                      => 'Bestätigungslink erneut per E-Mail zusenden',
            'TOKEN_NOT_FOUND'           => 'Verifizierungstoken existiert nicht / Konto wurde bereits verifiziert'
        ]
    ],

    'EMAIL' => [
        'INVALID'                       => 'Es gibt kein Konto für <strong>{{email}}</strong>.',
        'IN_USE'                        => 'Die E-Mail Adresse <strong>{{email}}</strong> wird bereits verwendet.',
        'VERIFICATION_REQUIRED'         => 'E-Mail (Bestätigung benötigt - Benutzen Sie eine echte E-Mail Adresse!)'
    ],

    'EMAIL_OR_USERNAME'                 => 'Benutzername oder E-mail Adresse',

    'FIRST_NAME'                        => 'Vorname',

    'HEADER_MESSAGE_ROOT'               => 'Sie sind als Root-Benutzer angemeldet.',

    'LAST_NAME'                         => 'Nachname',

    'LOCALE' => [
        'ACCOUNT'                       => 'Die Sprache und das Gebietsschema für Ihr Konto',
        'INVALID'                       => '<strong>{{locale}}</strong> ist kein gültiges Gebietsschema.'
    ],

    'LOGIN' => [
        '@TRANSLATION'                  => 'Anmelden',
        'ALREADY_COMPLETE'              => 'Sie sind bereits eingeloggt!',
        'SOCIAL'                        => 'Oder loggen Sie sich ein mit',
        'REQUIRED'                      => 'Sorry, Sie müssen angemeldet sein. Um auf diese Ressource zugreifen zu können.'
    ],

    'LOGOUT'                            => 'Ausloggen',

    'NAME'                              => 'Name',

    'NAME_AND_EMAIL'                    => 'Name und E-Mail',

    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION'               => 'Melden Sie sich in Ihr {{site_name}} Konto an oder registrieren Sie sich für ein neues Konto.',
            'SUBTITLE'                  => 'Registrieren Sie sich kostenlos oder melden Sie sich mit einem bestehenden Konto an.',
            'TITLE'                     => 'Lass uns anfangen!'
        ]
    ],

    'PASSWORD' => [
        '@TRANSLATION'                  => 'Passwort',

        'BETWEEN'                       => 'Zwischen {{min}}-{{max}} Zeichen',

        'CONFIRM'                       => 'Bestätige das Passwort',
        'CONFIRM_CURRENT'               => 'Bitte bestätige dein jetziges Passwort',
        'CONFIRM_NEW'                   => 'Neues Passwort bestätigen',
        'CONFIRM_NEW_EXPLAIN'           => 'Geben Sie Ihr neues Passwort erneut ein',
        'CONFIRM_NEW_HELP'              => 'Erforderlich, wenn Sie ein neues Passwort wählen',
        'CREATE'                        => [
            '@TRANSLATION'              => 'Passwort setzen',
            'PAGE'                      => 'Setzen Sie ein Passwort für den Account.',
            'SET'                       => 'Passwort setzen und anmelden'
        ],
        'CURRENT'                       => 'Aktuelles Passwort',
        'CURRENT_EXPLAIN'               => 'Sie müssen Ihr aktuelles Passwort bestätigen, um Änderungen vorzunehmen',

        'FORGOTTEN'                     => 'Passwort vergessen',
        'FORGET'                        => [
            '@TRANSLATION'              => 'Ich habe mein Passwort vergessen',

            'COULD_NOT_UPDATE'          => 'Das Passwort konnte nicht aktualisiert werden.',
            'EMAIL'                     => 'Bitte geben Sie die E-Mail-Adresse ein, mit der Sie sich registriert haben. Ein Link mit der Anweisungen zum Zurücksetzen Ihres Passworts wird Ihnen per E-Mail zugeschickt.',
            'EMAIL_SEND'                => 'Neue Passwort zurücksetzen E-Mail senden',
            'INVALID'                   => "Diese Anforderung zum Zurücksetzen des Passworts wurde nicht gefunden oder ist abgelaufen.Bitte versuchen Sie <a href=\'{{url}}\'>Ihre Anfrage erneut einzureichen<a>.",
            'PAGE'                      => 'Holen Sie sich einen Link, um Ihr Passwort zurückzusetzen.',
            'REQUEST_CANNED'            => 'Verlorene Passwortanforderung abgebrochen.',
            'REQUEST_SENT'              => 'Wenn die E-Mail <strong>{{email}}</strong> mit einem Account in unserem System übereinstimmt, wird ein Passwort-Reset-Link an <strong>{{email}}</strong> gesendet.'
        ],

        'HASH_FAILED'                   => 'Passwort Hashing fehlgeschlagen. Bitte kontaktieren Sie einen Administrator.',
        'INVALID'                       => 'Das aktuelle Passwort stimmt nicht mit dem Datensatz überein',
        'NEW'                           => 'Neues Passwort',
        'NOTHING_TO_UPDATE'             => 'Sie können nicht das gleiche Passwort zum Aktualisieren verwenden',

        'RESET' => [
            '@TRANSLATION'              => 'Passwort zurücksetzen',
            'CHOOSE'                    => 'Bitte wählen Sie ein neues Passwort, um fortzufahren.',
            'PAGE'                      => 'Wählen Sie ein neues Passwort für Ihr Konto.',
            'SEND'                      => 'Neues Passwort festlegen und anmelden'
        ],

        'UPDATED'                       => 'Konto Passwort aktualisiert'
    ],

    'PROFILE' => [
        'SETTINGS'                      => 'Profileinstellungen',
        'UPDATED'                       => 'Profileinstellungen aktualisiert'
    ],

    'RATE_LIMIT_EXCEEDED'               => 'Die grenze für diese Maßnahme wurde überschritten. Sie müssen weitere {{delay}} Sekunden warten, bevor Sie einen weiteren Versuch machen dürfen.',

    'REGISTER'                          => 'Registrieren',
    'REGISTER_ME'                       => 'Melden Sie mich an',
    'REGISTRATION'                      => [
        'BROKEN'                        => 'Es tut uns leid, es gibt ein Problem mit unserer Registrierung. Bitte kontaktieren Sie uns direkt für Hilfe.',
        'COMPLETE_TYPE1'                => 'Sie haben sich erfolgreich registriert. Sie können sich jetzt anmelden.',
        'COMPLETE_TYPE2'                => 'Sie haben sich erfolgreich registriert. Sie erhalten in Kürze eine Bestätigungs-E-Mail mit einem Link zur Aktivierung Ihres Kontos. Sie können sich nicht anmelden, bis Sie diesen Schritt abgeschlossen haben.',
        'DISABLED'                      => 'Es tut uns leid, Die Registrierung des Kontos ist deaktiviert.',
        'LOGOUT'                        => 'Es tut uns leid, Sie können kein neues Konto registrieren, während Sie angemeldet sind. Bitte melden Sie sich zuerst ab.',
        'WELCOME'                       => 'Die Registrierung ist schnell und einfach.'
    ],
    'REMEMBER_ME'                       => 'Erinnere dich an mich!',
    'REMEMBER_ME_ON_COMPUTER'           => 'Erinnere dich an mich auf diesem Computer (nicht für öffentliche Computer empfohlen)',

    'SIGN_IN_HERE'                      => 'Sie haben bereits einen Account? <a href="{{url}}">Melden Sie sich hier an.</a>',
    'SIGNIN'                            => 'Anmelden',
    'SIGNIN_OR_REGISTER'                => 'Anmelden oder registrieren',
    'SIGNUP'                            => 'Anmelden',

    'TOS'                               => 'Geschäftsbedingungen',
    'TOS_AGREEMENT'                     => 'Durch die Registrierung eines Kontos auf {{site_title}} akzeptieren Sie die <a {{link_attributes | raw}}> Bedingungen </a>.',
    'TOS_FOR'                           => 'Allgemeine Geschäftsbedingungen für {{title}}',

    'USERNAME' => [
        '@TRANSLATION'                  => 'Benutzername',

        'CHOOSE'                        => 'Wählen Sie einen eindeutigen Benutzernamen',
        'INVALID'                       => 'Ungültiger Benutzername',
        'IN_USE'                        => 'Benutzername <strong>{{user_name}}</strong> wird bereits verwendet.',
        'NOT_AVAILABLE'                 => "Benutzername <strong>{{user_name}}</strong> ist nicht verfügbar. Wähle einen anderen Namen, der klicken Sie auf 'vorschlagen'."
    ],

    'USER_ID_INVALID'                   => 'Die angeforderte Benutzer-ID existiert nicht.',
    'USER_OR_EMAIL_INVALID'             => 'Benutzername oder E-Mail-Adresse ist ungültig.',
    'USER_OR_PASS_INVALID'              => 'Benutzername oder Passwort ist ungültig.',

    'WELCOME'                           => 'Willkommen zurück, {{first_name}}'
];
