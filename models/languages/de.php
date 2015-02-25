<?php
/*

UserFrosting Version: 0.2.2
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

/*
%m1% - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Installer
$lang = array_merge($lang,array(
  "INSTALLER_INCOMPLETE"      => "Solange der Installationsprozess nicht abgeschlossen wurde, kann kein Master-Account angelegt werden.",
  "MASTER_ACCOUNT_EXISTS"     => "Der Master-Account existiert bereits!",
  "MASTER_ACCOUNT_NOT_EXISTS" => "Du kannst keinen neuen Account anlegen solange kein Master-Account angelegt wurde!",
  "CONFIG_TOKEN_MISMATCH" => "Leider ist dieser Konfigurationstoken nicht korrekt."
  ));

//Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" 		=> "Es wurde kein Benutzernamen eingegeben.",
	"ACCOUNT_SPECIFY_PASSWORD" 		=> "Es wurde kein Passwort eingegeben.",
	"ACCOUNT_SPECIFY_EMAIL"			=> "Es wurde keine E-Mail Adresse eingegeben.",
	"ACCOUNT_INVALID_EMAIL"			=> "Die E-Mail Adresse ist nicht gültig.",
  "ACCOUNT_INVALID_USER_ID"		=> "Der betreffende Benutzer existiert nicht.",
  "ACCOUNT_INVALID_PAY_TYPE"		=> "Ungültige Abrechnungsmethode. Die Abrechnungsmethode muss entweder 'ohne Abzüge' oder 'stündlich' sein.
",
	"ACCOUNT_USER_OR_EMAIL_INVALID"		=> "Benutzername oder E-Mail Adresse ist ungültig",
	"ACCOUNT_USER_OR_PASS_INVALID"		=> "Benutzername oder Passwort ist ungültig",
	"ACCOUNT_ALREADY_ACTIVE"		=> "Dieser Account wurde bereits aktiviert",
	"ACCOUNT_REGISTRATION_DISABLED" => "Die Registratur neuer Account wurde deaktiviert.",
  "ACCOUNT_INACTIVE"			=> "Dein Account ist in-aktive geschalten. Schau mal in deinem E-Mail / Spam nach der Aktivierungsanleitung",
	"ACCOUNT_DISABLED"			=> "Dieser Account wurde deaktiviert. Kontaktiere uns für weitere Informationen.",
  "ACCOUNT_USER_CHAR_LIMIT"		=> "Der Benutzername muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"ACCOUNT_DISPLAY_CHAR_LIMIT"		=> "Der angezeigter Name muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"ACCOUNT_PASS_CHAR_LIMIT"		=> "Das Passwort muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"ACCOUNT_TITLE_CHAR_LIMIT"		=> "Titel mmüssen mindestens %m1% und dürfen maximal %m2% Zeichen lang sein",
	"ACCOUNT_PASS_MISMATCH"			=> "Die eingegebenen Passwörter stimmen nicht überein",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS"	=> "Der angezeigter Name darf nur Alphanumerische Zeichen enthalten (ABCabc123).",
	"ACCOUNT_USERNAME_IN_USE"		=> "Der Benutzer %m1% wird bereits verwendet",
	"ACCOUNT_DISPLAYNAME_IN_USE"		=> "Der Anzeigename %m1% wird bereits verwendet",
	"ACCOUNT_EMAIL_IN_USE"			=> "Die E-Mail Adresse %m1% wird bereits verwendet",
	"ACCOUNT_LINK_ALREADY_SENT"		=> "Eine E-Mail zur Aktivierung dieses Accounts wurde bereits in den letzten %m1% Stunde(n) versendet.",
	"ACCOUNT_NEW_ACTIVATION_SENT"		=> "Es wurde soeben eine E-Mail zur Aktivierung deines Accounts zugesendet. Bitte prüfe deinen Posteingang / Spam.",
	"ACCOUNT_SPECIFY_NEW_PASSWORD"		=> "Bitte gib ein neues Passwort ein",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD"	=> "Bitte bestätige das neue Passwort.",
	"ACCOUNT_NEW_PASSWORD_LENGTH"		=> "Das neue Passwort muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"ACCOUNT_PASSWORD_INVALID"		=> "Das eingegebene Passwort stimmt nicht mit unseren Aufzeichnungen überein",
	"ACCOUNT_DETAILS_UPDATED"		=> "Account Details wurden upgedated",
	"ACCOUNT_ACTIVATION_MESSAGE"		=> "Der Account muss vor dem Login noch aktiviert werden. Die Aktivierung erfolgt über diesen Link: \n\n
	%m1%activate_user.php?token=%m2%",
	"ACCOUNT_CREATION_COMPLETE"		=> "Der Account des Benutzers %m1% wurde angelegt.",
  "ACCOUNT_ACTIVATION_COMPLETE"		=> "Dieser Account wurde aktiviert. Der Login ist jetzt möglich.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "Registrierung erfolgreich abgeschlossen. Der Login ist jetzt möglich.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "Registrierung erfolgreich abgeschlossen. Eine E-Mail zur Aktivierung des Accounts wurde an die angegebene Adresse versendet.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE"	=> "Dieses Passwort entspricht dem alten Passwort",
	"ACCOUNT_PASSWORD_UPDATED"		=> "Passwort geändert",
	"ACCOUNT_EMAIL_UPDATED"			=> "E-Mail Adresse geändert",
	"ACCOUNT_TOKEN_NOT_FOUND"		=> "Dieser Token existiert nicht / der Account wurde bereits aktiviert",
	"ACCOUNT_USER_INVALID_CHARACTERS"	=> "Der Benutzername darf nur alphanummerische Zeichen enthalten (ABCabc123)",
  "ACCOUNT_DELETE_MASTER"     => "Der Master-Account kann nicht gelöscht werden!",
  "ACCOUNT_DISABLE_MASTER"     => "Der Master-Account kann nicht deaktiviert werden!",
  "ACCOUNT_DISABLE_SUCCESSFUL"     => "Der Account wurde erfolgreich deaktiviert.",
  "ACCOUNT_ENABLE_SUCCESSFUL"     => "Der Account wurde erfolgreich aktiviert.",
  "ACCOUNT_DELETIONS_SUCCESSFUL"		=> "%m1% Benutzer wurden erfolgreich gelöscht",
	"ACCOUNT_MANUALLY_ACTIVATED"		=> "%m1%'s Accounts wurden manuell aktiviert",
	"ACCOUNT_DISPLAYNAME_UPDATED"		=> "Anzeigename geändert auf %m1%",
	"ACCOUNT_TITLE_UPDATED"			=> "%m1%'s Titel geändert auf %m2%",
	"ACCOUNT_GROUP_ADDED"		=> "Benutzer zur Gruppe %m1% hinzugefügt.",
	"ACCOUNT_GROUP_REMOVED"		=> "Benutzer von der Gruppe %m1% entfernt.",
	"ACCOUNT_GROUP_NOT_MEMBER"		=> "Benutzer ist kein Mitglied der Gruppe %m1%.",
	"ACCOUNT_GROUP_ALREADY_MEMBER"		=> "Benutzer ist bereits Mitglied der Gruppe %m1%.",
  "ACCOUNT_INVALID_USERNAME"		=> "Ungültiger Benutzername",
  "ACCOUNT_PRIMARY_GROUP_SET" => "Die Standard-Gruppe wurde erfolgreich geändert",
	));

//Configuration
$lang = array_merge($lang,array(
	"CONFIG_NAME_CHAR_LIMIT"		=> "Der Name der Seite muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"CONFIG_URL_CHAR_LIMIT"			=> "Die URL der Seite muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"CONFIG_EMAIL_CHAR_LIMIT"		=> "Die E-Mail Adresse muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"CONFIG_TITLE_CHAR_LIMIT"		=> "Die Bezeichnung für neue Benutzer muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
  "CONFIG_ACTIVATION_TRUE_FALSE"		=> "Die E-Mail Aktivierung muss entweder `ein-` oder `aus-` geschalten sein.",
	"CONFIG_REGISTRATION_TRUE_FALSE"		=> "Die Registrierung neuer Benutzer muss entweder `ein-` oder `aus-` geschalten sein.",
  "CONFIG_ACTIVATION_RESEND_RANGE"	=> "Aktivierungszeitraum muss zwischen %m1% und %m2% Stunden liegen.",
	"CONFIG_LANGUAGE_CHAR_LIMIT"		=> "Der Pfad zu den Sprachen-Files muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"CONFIG_LANGUAGE_INVALID"		=> "Die Sprachdatei `%m1%` wurde nicht gefunden.",
	"CONFIG_TEMPLATE_CHAR_LIMIT"		=> "Der Template-Pfad muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"CONFIG_TEMPLATE_INVALID"		=> "Das Template `%m1%` wurde nicht gefunden.",
	"CONFIG_EMAIL_INVALID"			=> "Die eingegebene E-Mail Adresse ist ungültig.",
	"CONFIG_INVALID_URL_END"		=> "Bitte die Endung / in der URL der Seite angeben.",
	"CONFIG_UPDATE_SUCCESSFUL"		=> "Die Konfigurationsdatei der Seite wurde upgedated. Die Änderungen treten in Kraft sobald eine neue Seite geladen wurde.",
	));

//Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN"		=> "Der Aktivierungstoken ist ungültig.",
  "FORGOTPASS_OLD_TOKEN"          => "Aktivierungstoken ist abgelaufen.",
  "FORGOTPASS_COULD_NOT_UPDATE"   => "Das Passwort konnte nicht geändert werden.",
	"FORGOTPASS_NEW_PASS_EMAIL"		=> "Es wurde ein neues Passwort zugesendet.",
	"FORGOTPASS_REQUEST_CANNED"		=> "Passwort-Wiederherstellung abgebrochen",
	"FORGOTPASS_REQUEST_EXISTS"		=> "Es läuft bereits eine Passwort-Wiederherstellung für diesen Account.",
	"FORGOTPASS_REQUEST_SUCCESS"		=> "Es wurde eine E-Mail mit den Anweisungen zur Wiederherstellung des Zugriffs versendet.",
	));

//Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR"				=> "Der Versandt der E-Mail hat nicht funktioniert. Server Administrator kontaktieren.",
	"MAIL_TEMPLATE_BUILD_ERROR"		=> "Die E-Mail konnte nicht erstellt werden",
	"MAIL_TEMPLATE_DIRECTORY_ERROR"		=> "Kein Zugriff auf den Mail-Templates Ordner. Versuche den Pfad des Ordners auf %m1% zu ändern",
	"MAIL_TEMPLATE_FILE_EMPTY"		=> "Die Template Datei ist leer... es gibt nichts zu übermitteln",
	));

//Miscellaneous
$lang = array_merge($lang,array(
  "PASSWORD_HASH_FAILED"  => "Password hashing fehlgeschlagen.  Bitte den Administrator der Seite kontaktieren.",
	"NO_DATA"				=> "Es wurden keine Daten gesendet",
  "CAPTCHA_FAIL"				=> "Ungültige Sicherheitsfrage",
	"CONFIRM"				=> "Bestätigt",
	"DENY"					=> "Verwährt",
	"SUCCESS"				=> "Success",
	"ERROR"					=> "Fehler",
	"NOTHING_TO_UPDATE"			=> "Es wurde kein Inhalt verändert",
	"SQL_ERROR"				=> "Böser böser SQL Fehler",
	"FEATURE_DISABLED"			=> "Dieses Feature ist derzeit nicht verfügbar",
	"PAGE_INVALID_ID"              => "Die angefragte Seite existiert nicht",
	"PAGE_INVALID"              => "Die angefragte Seite konnte in der Datenbank nicht gefunden werden",
  "PAGE_PRIVATE_TOGGLED"			=> "Diese Seite ist jetzt %m1%",
	"PAGE_ACCESS_REMOVED"			=> "Zugriff für %m1% Berechtigungsstufen entzogen",
	"PAGE_ACCESS_ADDED"			=> "Zugang für %m1% Berechtigungsstufen gewährt",
  "ACCESS_DENIED" => "Hmm, sieht aus als wären keine ausreichende Berechtigung vorhanden um diesen Inhalt anzuzeigen.",
  "LOGIN_REQUIRED" => "Um diesen Inhalt sehen zu können wird ein gültiger Login benötigt.",
	));

//Permissions
$lang = array_merge($lang,array(
  "GROUP_INVALID_ID"              => "Diese GruppenID existiert nicht",
	"PERMISSION_CHAR_LIMIT"			=> "Der Name der Berechtigung muss mindestens %m1% und darf maximal %m2% Zeichen lang sein",
	"PERMISSION_NAME_IN_USE"		=> "Berechtigung %m1% wird bereits verwendet",
	"PERMISSION_DELETION_SUCCESSFUL_NAME"		=> "Berechtigung '%m1%' gelöscht",
  "PERMISSION_DELETIONS_SUCCESSFUL"	=> "%m1% Berechtigungsstufen gelöscht",
	"PERMISSION_CREATION_SUCCESSFUL"	=> "Berechtigung `%m1%` erstellt",
	"GROUP_UPDATE"		=> "Gruppe `%m1%` erfolgreich geändert.",
	"PERMISSION_REMOVE_PAGES"		=> "Zugang zu %m1% Seiten gesperrt",
	"PERMISSION_ADD_PAGES"			=> "Zugang zu %m1% Seiten gewährt",
	"PERMISSION_REMOVE_USERS"		=> "%m1% Benutzer erfolgreich gelöscht",
	"PERMISSION_ADD_USERS"			=> "%m1% Benutzer erfolgreich hinzugefügt",
	"CANNOT_DELETE_PERMISSION_GROUP" => "Die Gruppe '%m1%' kann nicht gelöscht werden",
	));

//Private Messages
$lang = array_merge($lang,array(
  "PM_RECEIVER_DELETION_SUCCESSFUL"   => "Nachricht gelöscht",
));
?>
