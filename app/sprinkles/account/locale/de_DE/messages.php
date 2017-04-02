<?php

/**
 * de_DE
 *
 * German message token translations for the 'account' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author @X-Anonymous-Y
 *
 */

return [
    "ACCOUNT" => [
        "@TRANSLATION"                  => "Konto",

        "ACCESS_DENIED"                 => "Hmm, sieht aus als hätten Sie keine Berechtigung, um dies zu tun.",

        "DISABLED"                      => "Dieses Konto wurde deaktiviert. Bitte Kontaktieren Sie uns für weitere Informationen.",

        "EMAIL_UPDATED"                 => "E-Mail-Adresse aktualisiert.",

        "INVALID"                       => "Dieses Konto existiert nicht. Es wurde möglicherweise gelöscht. Bitte kontaktieren Sie uns für weitere Informationen.",

        "MASTER_NOT_EXISTS"             => "Sie können kein neues Konto anlegen solange kein Root-Konto angelegt wurde!",
        "MY"                            => "Mein Konto",

        "SESSION_COMPROMISED"           => "Ihre Sitzung wurde beeinträchtigt. Sie sollten sich auf allen Geräten abmelden, sich dann wieder anmelden und sicherstellen, dass Ihre Daten nicht manipuliert wurden.",
        "SESSION_COMPROMISED_TITLE"     => "Ihr Konto wurde möglicherweise beeinträchtigt",
        "SESSION_EXPIRED"               => "Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.",

        "SETTINGS" => [
            "@TRANSLATION"              => "Kontoeinstellungen",
            "DESCRIPTION"               => "Aktualisieren Sie Ihre Kontoeinstellungen, einschließlich E-Mail, Name und Passwort.",
            "UPDATED"                   => "Kontoeinstellungen aktualisiert"
        ],

        "TOOLS"                         => "Konto-Werkzeuge",

        "UNVERIFIED"                    => "Ihr Konto wurde noch nicht bestätigt. Überprüfen Sie Ihr E-Mails/Spam-Ordner für die Konto-Aktivierungsanleitung.",

        "VERIFICATION" => [
            "NEW_LINK_SENT"             => "Wir haben einen neuen Bestätigungslink an {{email}} gesendet. Überprüfen Sie Ihr E-Mail/Spam-Ordner oder versuchen Sie es später noch einmal.",
            "RESEND"                    => "Bestätigungsmail erneut senden",
            "COMPLETE"                  => "Sie haben Ihr Konto erfolgreich Verifiziert. Sie können sich jetzt anmelden.",
            "EMAIL"                     => "Bitte geben Sie die E-Mail-Adresse ein, mit der Sie sich registriert haben, Überprüfen Sie Ihr E-Mails/Spam-Ordner für die Bestätigungs-E-Mail.",
            "PAGE"                      => "Senden Sie die Bestätigungs-E-Mail erneut für Ihr neues Konto.",
            "SEND"                      => "Senden Sie eine neue E-Mail mit dem Bestätigungslink für mein Konto",
            "TOKEN_NOT_FOUND"           => "Verifizierungstoken existiert nicht / Konto wurde bereits verifiziert",
        ]
    ],

    "EMAIL" => [
        "INVALID"                       => "Es gibt kein Konto für <strong>{{email}}</strong>.",
        "IN_USE"                        => "E-Mail <strong>{{email}}</strong> wird bereits verwendet."
    ],

    "EMAIL_OR_USERNAME"                 => "Benutzername oder E-mail Adresse",

    "FIRST_NAME"                        => "Vorname",

    "HEADER_MESSAGE_ROOT"               => "Sie sind als Root-Benutzer angemeldet.",

    "LAST_NAME"                         => "Nachname",

    "LOCALE" => [
        "ACCOUNT"                       => "Die Sprache und das Gebietsschema für Ihr Konto",
        "INVALID"                       => "<strong>{{locale}}</strong> ist kein gültiges Gebietsschema."
    ],

    "LOGIN" => [
        "@TRANSLATION"                  => "Anmelden",
        "ALREADY_COMPLETE"              => "Sie sind bereits eingeloggt!",
        "SOCIAL"                        => "Oder loggen Sie sich ein mit",
        "REQUIRED"                      => "Sorry, Sie müssen angemeldet sein. Um auf diese Ressource zugreifen zu können."
    ],

    "LOGOUT"                            => "Ausloggen",

    "NAME"                              => "Name",

    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION"               => "Melden Sie sich in Ihr {{site_name}} Konto an oder registrieren Sie sich für ein neues Konto.",
            "SUBTITLE"                  => "Registrieren Sie sich kostenlos oder melden Sie sich mit einem bestehenden Konto an.",
            "TITLE"                     => "Lass uns anfangen!",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION"                  => "Passwort",

        "BETWEEN"                       => "Zwischen {{min}}-{{max}} Zeichen",

        "CONFIRM"                       => "Bestätige das Passwort",
        "CONFIRM_CURRENT"               => "Bitte bestätige dein jetziges Passwort",
        "CONFIRM_NEW"                   => "Neues Passwort bestätigen",
        "CONFIRM_NEW_EXPLAIN"           => "Geben Sie Ihr neues Passwort erneut ein",
        "CONFIRM_NEW_HELP"              => "Erforderlich, wenn Sie ein neues Passwort wählen",
        "CURRENT"                       => "Aktuelles Passwort",
        "CURRENT_EXPLAIN"               => "Sie müssen Ihr aktuelles Passwort bestätigen, um Änderungen vorzunehmen",

        "FORGOTTEN"                     => "Passwort vergessen",
        "FORGET" => [
            "@TRANSLATION"              => "Ich habe mein Passwort vergessen",

            "COULD_NOT_UPDATE"          => "Das Passwort konnte nicht aktualisiert werden.",
            "EMAIL"                     => "Bitte geben Sie die E-Mail-Adresse ein, mit der Sie sich registriert haben. Ein Link mit der Anweisungen zum Zurücksetzen Ihres Passworts wird Ihnen per E-Mail zugeschickt.",
            "EMAIL_SEND"                => "Neue Passwort zurücksetzen E-Mail senden",
            "INVALID"                   => "Diese Anforderung zum Zurücksetzen des Passworts wurde nicht gefunden oder ist abgelaufen.Bitte versuchen Sie <a href=\'{{url}}\'>Ihre Anfrage erneut einzureichen<a>.",
            "PAGE"                      => "Holen Sie sich einen Link, um Ihr Passwort zurückzusetzen.",
            "REQUEST_CANNED"            => "Verlorene Passwortanforderung abgebrochen.",
            "REQUEST_SENT"              => "Ein Passwort-Reset-Link wurde an {{email}} gesendet."
        ],

        "RESET" => [
            "@TRANSLATION"              => "Passwort zurücksetzen",
            "CHOOSE"                    => "Bitte wählen Sie ein neues Passwort, um fortzufahren.",
            "PAGE"                      => "Wählen Sie ein neues Passwort für Ihr Konto.",
            "SEND"                      => "Neues Passwort festlegen und Anmelden"
        ],

        "HASH_FAILED"                   => "Passwort Hashing fehlgeschlagen. Bitte kontaktieren Sie einen Administrator.",
        "INVALID"                       => "Das aktuelle Passwort stimmt nicht mit dem Datensatz überein",
        "NEW"                           => "Neues Passwort",
        "NOTHING_TO_UPDATE"             => "Sie können nicht das gleiche Passwort zum Aktualisieren verwenden",
        "UPDATED"                       => "Konto Passwort aktualisiert"
    ],

    "PROFILE" => [
        "SETTINGS"                      => "Profileinstellungen",
        "UPDATED"                       => "Profileinstellungen aktualisiert"
    ],

    "REGISTER"                          => "Registrieren",
    "REGISTER_ME"                       => "Melden Sie mich an",

    "REGISTRATION" => [
        "BROKEN"                        => "Es tut uns leid, es gibt ein Problem mit unserer Registrierung. Bitte kontaktieren Sie uns direkt für Hilfe.",
        "COMPLETE_TYPE1"                => "Sie haben sich erfolgreich registriert. Sie können sich jetzt anmelden.",
        "COMPLETE_TYPE2"                => "Sie haben sich erfolgreich registriert. Sie erhalten in Kürze eine Bestätigungs-E-Mail mit einem Link zur Aktivierung Ihres Kontos. Sie können sich nicht anmelden, bis Sie diesen Schritt abgeschlossen haben.",
        "DISABLED"                      => "Es tut uns leid, Die Registrierung des Kontos ist deaktiviert.",
        "LOGOUT"                        => "Es tut uns leid, Sie können kein neues Konto registrieren, während Sie angemeldet sind. Bitte melden Sie sich zuerst ab.",
        "WELCOME"                       => "Die Registrierung ist schnell und einfach."
    ],

    "RATE_LIMIT_EXCEEDED"               => "Die grenze für diese Maßnahme wurde überschritten. Sie müssen weitere {{delay}} Sekunden warten, bevor Sie einen weiteren Versuch machen dürfen.",
    "REMEMBER_ME"                       => "Erinnere dich an mich!",
    "REMEMBER_ME_ON_COMPUTER"           => "Erinnere dich an mich auf diesem Computer (nicht für öffentliche Computer empfohlen)",

    "SIGNIN"                            => "Anmelden",
    "SIGNIN_OR_REGISTER"                => "Anmelden oder registrieren",
    "SIGNUP"                            => "Anmelden",

    "TOS"                               => "Geschäftsbedingungen",
    "TOS_AGREEMENT"                     => "Durch die Registrierung eines Kontos auf {{site_title}} akzeptieren Sie die <a {{link_attributes | raw}}> Bedingungen </a>.",
    "TOS_FOR"                           => "Allgemeine Geschäftsbedingungen für {{title}}",

    "USERNAME" => [
        "@TRANSLATION"                  => "Benutzername",

        "CHOOSE"                        => "Wählen Sie einen eindeutigen Benutzernamen",
        "INVALID"                       => "Ungültiger Benutzername",
        "IN_USE"                        => "Benutzername <strong>{{user_name}}</strong> wird bereits verwendet.",
        "NOT_AVAILABLE"                 => "Benutzername <strong>{{user_name}}</strong> ist nicht verfügbar. Wähle einen anderen Namen, der klicken Sie auf 'vorschlagen'."
    ],

    "USER_ID_INVALID"                   => "Die angeforderte Benutzer-ID existiert nicht.",
    "USER_OR_EMAIL_INVALID"             => "Benutzername oder E-Mail-Adresse ist ungültig.",
    "USER_OR_PASS_INVALID"              => "Benutzername oder Passwort ist ungültig.",

    "WELCOME"                           => "Willkommen zurück, {{first_name}}"
];
