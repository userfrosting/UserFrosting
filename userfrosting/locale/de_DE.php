<?php

/*
{{name}} - Dynamische Marker, die zur Laufzeit durch den entsprechenden Index ersetzt werden.
*/

$lang = array();

// Website-Inhalt
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "Die Registrierung ist schnell und einfach.",
	"MENU_USERS" => "Benutzer",
	"MENU_CONFIGURATION" => "Konfiguration",
	"MENU_SITE_SETTINGS" => "Website-Einstellungen",
	"MENU_GROUPS" => "Gruppen",
	"HEADER_MESSAGE_ROOT" => "Sie sind als Root-Benutzer angemeldet."
]);

// Installation
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "Solange der Installationsprozess nicht abgeschlossen wurde, kann kein Root-Account angelegt werden.",
	"MASTER_ACCOUNT_EXISTS" => "Der Root-Account existiert bereits!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "Du kannst kein neuen Account anlegen solange kein Root-Account angelegt wurde!",
	"CONFIG_TOKEN_MISMATCH" => "Leider ist dieser Konfigurationstoken nicht korrekt."
));

// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Bitte geben Sie Ihren Benutzernamen ein.",
	"ACCOUNT_SPECIFY_USERNAME_OR_EMAIL" => "Bitte geben Sie Ihren Benutzernamen oder Ihre E-Mail-Adresse ein.",// must be inserted in the code // for login // If email login is enabled.
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Bitte geben Sie Ihren Anzeigenamen ein.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Bitte geben Sie ihr Passwort ein.",
	"ACCOUNT_SPECIFY_EMAIL" => "Bitte geben Sie Ihre E-Mail-Adresse ein.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Bitte geben Sie den Captcha-Code ein.",
	"ACCOUNT_SPECIFY_LOCALE" => "Bitte wählen Sie eine gültige Sprache aus.",
	"ACCOUNT_INVALID_EMAIL" => "Ungültige E-Mail-Adresse.",
	"ACCOUNT_INVALID_USERNAME" => "Ungültiger Benutzername.",
	"ACCOUNT_INVALID_USER_ID" => "Die angeforderte Benutzer-ID existiert nicht.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Benutzername oder E-Mail-Adresse ist ungültig.",
	"ACCOUNT_EMAIL_USER_OR_PASS_INVALID" => "E-Mail-Adresse, Benutzername oder Passwort ist ungültig.",// must be inserted in the code // for login // If email login is enabled.
	"ACCOUNT_USER_OR_PASS_INVALID" => "Benutzername oder Passwort ist ungültig.",
	"ACCOUNT_ALREADY_ACTIVE" => "Dieser Account ist bereits aktiviert.",
	"ACCOUNT_REGISTRATION_DISABLED" => "Die Account-Registrierung wurde deaktiviert.",
	"ACCOUNT_REGISTRATION_LOGOUT" => "Sie können kein neuen Account registrieren während Sie angemeldet sind. Bitte melden Sie sich erst ab.",
	"ACCOUNT_INACTIVE" => "Ihr Account ist inaktiv. Überprüfen Sie Ihr E-Mail/Spam-Ordner für die Account-Aktivierungsanleitung.",
	"ACCOUNT_DISABLED" => "Dieser Account wurde deaktiviert. Bitte Kontaktieren Sie uns für weitere Informationen.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Ihr Benutzername muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Ihr Anzeigename muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "Das Passwort muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "Die E-Mail-Adresse muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Der Titel muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_PASS_MISMATCH" => "Das Passwort und das Bestätigungs Passwort stimmen nicht überein.",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Ihr Anzeigename darf nur Alphanumerische Zeichen enthalten.",
	"ACCOUNT_USERNAME_IN_USE" => "Der Benutzername {{user_name}} wird bereits verwendet.",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Der Anzeigename {{display_name}} wird bereits verwendet.",
	"ACCOUNT_EMAIL_IN_USE" => "Die E-Mail-Adresse {{email}} wird bereits verwendet.",
	"ACCOUNT_LINK_ALREADY_SENT" => "Eine E-Mail zur Aktivierung dieses Accounts wurde bereits in den letzten {{resend_activation_threshold}} Sekunde(n) versendet. Überprüfen Sie Ihr E-Mail/Spam-Ordner oder versuchen Sie es später noch einmal.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Wir haben Ihnen einen neuen Aktivierungslink gesendet. Überprüfen Sie Ihr E-Mail/Spam-Ordner.",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Bitte geben Sie Ihr neues Passwort ein.",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Bitte bestätigen Sie Ihr neues Passwort.",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "Das neue Passwort muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"ACCOUNT_PASSWORD_INVALID" => "Das Passwort stimmt nicht mit dem bei uns gespeichert Passwort überein.",
	"ACCOUNT_DETAILS_UPDATED" => "Account-Daten für {{user_name}} aktualisiert.",
	"ACCOUNT_CREATION_COMPLETE" => "Account für {{user_name}} wurde erstellt.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Sie haben Ihr Account erfolgreich aktiviert. Sie können sich jetzt anmelden.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Sie haben sich erfolgreich registriert. Sie können sich jetzt anmelden.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Sie haben sich erfolgreich registriert. Sie erhalten in Kürze ein Aktivierungslink. Sie müssen Ihr Account vor der Anmeldung erst aktivieren überprüfen Sie dazu Ihr E-Mail/Spam-Ordner",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Dieses Passwort entspricht dem alten Passwort.",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Bitte bestätigen Sie Ihr aktuelles Passwort.",
	"ACCOUNT_SETTINGS_UPDATED" => "Einstellungen aktualisiert.",
	"ACCOUNT_PASSWORD_UPDATED" => "Passwort aktualisiert.",
	"ACCOUNT_EMAIL_UPDATED" => "E-Mail-Adresse aktualisiert.",
	"ACCOUNT_TOKEN_NOT_FOUND" => "Token existiert nicht / Account ist bereits aktiviert.",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "Benutzernamen dürfen nur Alphanumerische Zeichen enthalten.",
	"ACCOUNT_DELETE_MASTER" => "Sie können den Root-Account nicht löschen!",
	"ACCOUNT_DISABLE_MASTER" => "Sie können den Root-Account nicht deaktivieren!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "Account von {{user_name}} wurde erfolgreich deaktiviert.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "Account von {{user_name}} wurde erfolgreich aktiviert.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "Benutzer{{user_name}} wurde erfolgreich gelöscht.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "{{user_name}}'s Account wurde manuell aktiviert.",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "{{user_name}}'s Anzeigename geändert zu {{display_name}}",
	"ACCOUNT_TITLE_UPDATED" => "{{user_name}}'s Titel geändert zu {{title}}",
	"ACCOUNT_GROUP_ADDED" => "Benutzer zur Gruppe '{{name}}' hinzugefügt.",
	"ACCOUNT_GROUP_REMOVED" => "Benutzer aus Gruppe '{{name}}' entfernt.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "Benutzer ist kein Mitglied der Gruppe '{{name}}'",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "Benutzer ist bereits Mitglied der Gruppe '{{name}}'",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Primäre Gruppe für {{user_name}} erfolgreich gesetzt.",
	"ACCOUNT_WELCOME" => "Willkommen Zurück, {{display_name}}"
));

// Validierung
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "Das Feld {{self}} muss angegeben werden.",
	"VALIDATE_BOOLEAN" => "Der Wert für {{self}} muss entweder '0' oder '1' sein.",
	"VALIDATE_INTEGER" => "Der Wert für {{self}} muss eine ganze Zahl sein.",
	"VALIDATE_ARRAY" => "Die Werte für {{self}} müssen in einem Array sein."
));

// Konfiguration
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "Sie versuchen, die Einstellungen für Plugin '{{plugin}}' zu aktualisieren es gibt aber kein Plugin mit diesem Namen.",
	"CONFIG_SETTING_INVALID" => "Sie versuchen, die Einstellung '{{name}}' für Plugin '{{plugin}}' zu aktualisieren, es ist aber nicht vorhanden.",
	"CONFIG_NAME_CHAR_LIMIT" => "Website-Name muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"CONFIG_URL_CHAR_LIMIT" => "Website-URL muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"CONFIG_EMAIL_CHAR_LIMIT" => "Website E-Mail-Adresse muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"CONFIG_TITLE_CHAR_LIMIT" => "Titel für neue Benutzer muss zwischen {{min}} und {{max}} Zeichen lang sein.",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "E-Mail-Aktivierung muss entweder '0' oder '1' sein.",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "Benutzer-Registrierung muss entweder '0' oder '1' sein.",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "Aktivierungszeitraum muss zwischen {{min}} und {{max}} Stunde(n) sein.",
	"CONFIG_EMAIL_INVALID" => "Die eingegebene E-Mail-Adresse ist nicht gültig.",
	"CONFIG_UPDATE_SUCCESSFUL" => "Ihre Website-Konfiguration wurde aktualisiert. Möglicherweise müssen Sie eine neue Seite laden damit alle Einstellungen wirksam werden.",
	"MINIFICATION_SUCCESS" => "CSS und JS Dateien für alle Seitengruppen erfolgreich minimiert und zusammengeführt."
));

// Passwort Vergessen
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Ungültiger Aktivierungs-Token",
	"FORGOTPASS_OLD_TOKEN" => "Aktivierungs-Token ist abgelaufen.",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Passwort konnte nicht aktualisiert werden.",
	"FORGOTPASS_NEW_PASS_EMAIL" => "Wir haben Ihnen ein neues Passwort gesendet.",
	"FORGOTPASS_REQUEST_CANNED" => "Passwort-Wiederherstellung abgebrochen.",
	"FORGOTPASS_REQUEST_EXISTS" => "Es läuft bereits eine Passwort-Wiederherstellung für diesen Account.",
	"FORGOTPASS_REQUEST_SUCCESS" => "Es wurde eine E-Mail mit den Anweisungen zur Wiederherstellung des Zugriffs versendet."
));

// e-Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "Fataler Fehler beim E-Mail Versandt. Bitte Kontaktieren Sie einen Administrator der Website.",
));

// Verschiedenes
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Passwort-Hashing gescheitert. Bitte Kontaktieren Sie einen Administrator der Website.",
	"NO_DATA" => "Keine Daten/schlechte Daten gesendet",
	"CAPTCHA_FAIL" => "Ungültiger Captcha-Code",
	"CONFIRM" => "Bestätigen",
	"DENY" => "Verweigern",
	"SUCCESS" => "Erfolgreich",
	"ERROR" => "Fehler",
	"SERVER_ERROR" => "Hoppla, sieht aus als hätte der Server möglicherweise gepatzt. Wenn Sie ein Administrator sind, überprüfen Sie bitte die PHP-Fehlerprotokolle. Oder Kontaktieren Sie Bitte einen Administrator der Website.",
	"NOTHING_TO_UPDATE" => "Nichts zu aktualisieren.",
	"SQL_ERROR" => "Schwerer SQL-Fehler.",
	"FEATURE_DISABLED" => "Diese Funktion ist derzeit deaktiviert.",
	"ACCESS_DENIED" => "Hmm, sieht aus als hätten Sie keine Berechtigung, um diesen Inhalt zu sehen.",
	"LOGIN_REQUIRED" => "Sorry, Sie müssen angemeldet sein. Um auf diese Ressource zugreifen zu können.",
	"LOGIN_ALREADY_COMPLETE" => "Sie sind bereits angemeldet!"
));

// Berechtigungen
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "Die angeforderte Gruppen-ID existiert nicht.",
	"GROUP_NAME_CHAR_LIMIT" => "Gruppennamen müssen zwischen {{min}} und {{max}} Zeichen lang sein.",
	"GROUP_NAME_IN_USE" => "Gruppenname '{{name}}' wird bereits verwendet",
	"GROUP_DELETION_SUCCESSFUL" => "Gruppe '{{name}}' Erfolgreich gelöscht.",
	"GROUP_CREATION_SUCCESSFUL" => "Gruppe '{{name}}' Erfolgreich erstellt.",
	"GROUP_UPDATE" => "Gruppe '{{name}}' erfolgreich aktualisiert.",
	"CANNOT_DELETE_GROUP" => "Die Gruppe '{{name}}' kann nicht gelöscht werden.",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "Die Gruppe '{{name}}' kann nicht gelöscht werden, da sie als Standard-Primärgruppe für neue Benutzer festgelegt ist. Bitte wählen Sie zuerst eine andere Standard-Primärgruppe aus."
));

return $lang;
