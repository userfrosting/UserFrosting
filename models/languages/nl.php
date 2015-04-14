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
    "INSTALLER_INCOMPLETE"      => "Je kan geen master account aanmaken tot de installatie afgerond is.",
    "MASTER_ACCOUNT_EXISTS"     => "Er is al een master account aangemaakt.",
    "MASTER_ACCOUNT_NOT_EXISTS" => "Je kan nog geen account aanmaken tot er een master account is.",
    "CONFIG_TOKEN_MISMATCH" => "Sorry, je configuratie token is niet geldig."
    ));

//Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" 		=> "Voer je gebruikersnaam in",
	"ACCOUNT_SPECIFY_PASSWORD" 		=> "Voer je wachtwoord in",
	"ACCOUNT_SPECIFY_EMAIL"			=> "Voer je emailadres in",
	"ACCOUNT_INVALID_EMAIL"			=> "Ongeldig emailadres",
    "ACCOUNT_INVALID_USER_ID"		=> "De opgevraagde gebruikers id bestaat niet.",
    "ACCOUNT_INVALID_PAY_TYPE"		=> "Invalid pay type.  Pay type must be either 'deduct fee' or 'hourly'.",
	"ACCOUNT_USER_OR_EMAIL_INVALID"		=> "Gebruikersnaam of emailadres is niet correct",
	"ACCOUNT_USER_OR_PASS_INVALID"		=> "Gebruikersnaam of wachtwoord is niet correct",
	"ACCOUNT_ALREADY_ACTIVE"		=> "Je account is al geactiveerd",
	"ACCOUNT_REGISTRATION_DISABLED" => "Het registreren van nieuwe accounts is op dit moment uitgeschakeld.",
    "ACCOUNT_INACTIVE"			=> "Je account is niet actief. Controleer je email / spam map voor de activatie instructies.",
	"ACCOUNT_DISABLED"			=> "Deze account is gedeactiveerd.  Neem contact met ons op voor meer informatie.",
    "ACCOUNT_USER_CHAR_LIMIT"		=> "Je gebruikersnaam moet tussen de %m1% en %m2% tekens bevatten.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT"		=> "Je weergegeven naam moet tussen de %m1% en %m2% tekens bevatten.",
	"ACCOUNT_PASS_CHAR_LIMIT"		=> "Je wachtwoord moet tussen de %m1% en %m2% tekens bevatten.",
	"ACCOUNT_TITLE_CHAR_LIMIT"		=> "Titels moeten tussen de %m1% en %m2% tekens bevatten.",
	"ACCOUNT_PASS_MISMATCH"			=> "Je wachwoord komt niet overeen.",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS"	=> "De weergegeven naam kan enkel alpha-numeric karakters bevatten.",
	"ACCOUNT_USERNAME_IN_USE"		=> "De volgende gebruikersnaam is al in gebruik: %m1%",
	"ACCOUNT_DISPLAYNAME_IN_USE"		=> " naam %m1% is al in gebruik",
	"ACCOUNT_EMAIL_IN_USE"			=> "Email %m1% is al in gebruik",
	"ACCOUNT_LINK_ALREADY_SENT"		=> "Een activatie email is al verzonden naar dit emailadres in de afgelopen %m1% uur.",
	"ACCOUNT_NEW_ACTIVATION_SENT"		=> "We hebben je een nieuwe activatie emailverzonden. Controleer je email.",
	"ACCOUNT_SPECIFY_NEW_PASSWORD"		=> "Voer een nieuw wachtwoord in",	
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD"	=> "Bevestig het nieuwe wachtwoord",
	"ACCOUNT_NEW_PASSWORD_LENGTH"		=> "Nieuwe wachwoord moet tussen de %m1% en %m2% tekens bevatten.",	
	"ACCOUNT_PASSWORD_INVALID"		=> "Het wachtwoord komt niet overeen met het wachtwoord dat bij ons bekend staat.",	
	"ACCOUNT_DETAILS_UPDATED"		=> "Account details zijn bijgewerkt.",
	"ACCOUNT_ACTIVATION_MESSAGE"		=> "Je moet je account activeren voordat je kan inloggen. Volg de onderstaande link om je account te activeren. \n\n
	%m1%activate_user.php?token=%m2%",							
	"ACCOUNT_CREATION_COMPLETE"		=> "Account voor nieuwe gebruiker %m1% is met succes aangemaakt.",
    "ACCOUNT_ACTIVATION_COMPLETE"		=> "Je hebt met succes je account geactiveerd. Je kan nu inloggen.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "Je hebt met succes je account geregistreerd. Je kan nu inloggen.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "Je hebt met succes je account geregistreerd. Je ontvangt spoedig een activeringsemail. Je account moet eerst geactiveerd worden voordat je kan inloggen.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE"	=> "Je kan je account niet aanpassen naar hetzelfde wachtwoord.",
	"ACCOUNT_PASSWORD_UPDATED"		=> "Account wachtwoord is aangepast",
	"ACCOUNT_EMAIL_UPDATED"			=> "Account email aangepast",
	"ACCOUNT_TOKEN_NOT_FOUND"		=> "Token bestaat niet / Account is al actief",
	"ACCOUNT_USER_INVALID_CHARACTERS"	=> "Gebruikersnaam kan enkel alfanumerieke karakters bevatten.",
    "ACCOUNT_DELETE_MASTER"     => "Je kan de master account niet verwijderen!",
    "ACCOUNT_DISABLE_MASTER"     => "Je kan de master account niet uitzetten!",
    "ACCOUNT_DISABLE_SUCCESSFUL"     => "Account is met succes uitgezet.",
    "ACCOUNT_ENABLE_SUCCESSFUL"     => "Account is met succes aangezet.",
    "ACCOUNT_DELETIONS_SUCCESSFUL"		=> "Je hebt met succes %m1% gebruikers verwijderd",
	"ACCOUNT_MANUALLY_ACTIVATED"		=> "%m1%'s account is handmatig geactiveerd",
	"ACCOUNT_DISPLAYNAME_UPDATED"		=> "Weergegeven naam aangepast naar %m1%",
	"ACCOUNT_TITLE_UPDATED"			=> "%m1%'s titel is aangepast naar %m2%",
	"ACCOUNT_GROUP_ADDED"		=> "Gebruiker toegevoegd aan de groep %m1%.",
	"ACCOUNT_GROUP_REMOVED"		=> "Gebruiker is verwijderd van de groep %m1%.",
	"ACCOUNT_GROUP_NOT_MEMBER"		=> "Gebruiker is niet een lid van de groep %m1%.",
	"ACCOUNT_GROUP_ALREADY_MEMBER"		=> "Gebruiker is al een lid van de groep %m1%.",
    "ACCOUNT_INVALID_USERNAME"		=> "Ongeldig gebruikersnaam",
    "ACCOUNT_PRIMARY_GROUP_SET" => "De primaire groep van de gebruiker is met succes ingesteld.",
	));

//Configuration
$lang = array_merge($lang,array(
	"CONFIG_NAME_CHAR_LIMIT"		=> "Site naam moet tussen de %m1% en %m2% tekens bevatten",
	"CONFIG_URL_CHAR_LIMIT"			=> "Site url moet tussen de %m1% en %m2% tekens bevatten",
	"CONFIG_EMAIL_CHAR_LIMIT"		=> "Site email moet tussen de %m1% en %m2% tekens bevatten",
	"CONFIG_TITLE_CHAR_LIMIT"		=> "Nieuwe gebruikerstitel moet tussen de %m1% en %m2% tekens bevatten",
    "CONFIG_ACTIVATION_TRUE_FALSE"		=> "Email activeren moet `true` of `false` zijn",
	"CONFIG_REGISTRATION_TRUE_FALSE"		=> "Gebruikersregistratie moet `true` of `false` zijn",
    "CONFIG_ACTIVATION_RESEND_RANGE"	=> "Tijdslimiet op activatie moet tussen de %m1% en %m2% uur zijn",
	"CONFIG_LANGUAGE_CHAR_LIMIT"		=> "Taal pad moet tussen de %m1% en %m2% tekens bevatten",
	"CONFIG_LANGUAGE_INVALID"		=> "Er is geen bestand voor de taal `%m1%` gevonden.",
	"CONFIG_TEMPLATE_CHAR_LIMIT"		=> "Template pad moet tussen de %m1% en %m2% tekens bevatten",
	"CONFIG_TEMPLATE_INVALID"		=> "Er is geen bestand beschikbaar voor de template `%m1%`",
	"CONFIG_EMAIL_INVALID"			=> "Het ingevulde emailadres is niet geldig",
	"CONFIG_INVALID_URL_END"		=> "Vergeet niet de / in de websites URL",
	"CONFIG_UPDATE_SUCCESSFUL"		=> "Je site configuratie is met succes aangepast. Vergeet niet de pagina te herladen om de wijzigingen te zijn.",
	));

//Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN"		=> "Je activeringstoken is niet meer geldig",
    "FORGOTPASS_OLD_TOKEN"          => "Token is verlopen",
    "FORGOTPASS_COULD_NOT_UPDATE"   => "Kon wachtwoord niet updaten",
	"FORGOTPASS_NEW_PASS_EMAIL"		=> "We hebben je een nieuw wachtwoord opgestuurd per email",
	"FORGOTPASS_REQUEST_CANNED"		=> "Wachtwoordverzoek geannuleerd",
	"FORGOTPASS_REQUEST_EXISTS"		=> "Er loopt al een wachtwoordverzoek voor dit account",
	"FORGOTPASS_REQUEST_SUCCESS"		=> "We hebben instructies toegestuurd om toegang tot je account terug te krijgen",
	));


//Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR"				=> "Fatal fout bij het versturen van email. Neem contact op met de server administrator.",
	"MAIL_TEMPLATE_BUILD_ERROR"		=> "Fout bij het maken van de email template",
	"MAIL_TEMPLATE_DIRECTORY_ERROR"		=> "Niet mogelijk om de email template map te openen. Probeer de instelling aan te passen naar %m1%",
	"MAIL_TEMPLATE_FILE_EMPTY"		=> "Template bestand is leeg... Er niets om te versturen",
	));

//Miscellaneous
$lang = array_merge($lang,array(
    "PASSWORD_HASH_FAILED"  => "Wachtwoord hashing mislukt.  Neem contact op met de website administrator.",
	"NO_DATA"				=> "Geen of ongeldige data verzonden.",
    "CAPTCHA_FAIL"				=> "Beveiligingsvraag mislukt.",
	"CONFIRM"				=> "Bevestig",
	"DENY"					=> "Annuleer",
	"SUCCESS"				=> "Succes",
	"ERROR"					=> "Fout",
	"NOTHING_TO_UPDATE"			=> "Niets te updaten",
	"SQL_ERROR"				=> "Fatale SQL fout",
	"FEATURE_DISABLED"			=> "Deze functie is uitgeschakeld.",
	"PAGE_INVALID_ID"              => "De opgevraagde pagina id bestaat niet",
	"PAGE_INVALID"              => "De opgevraagde pagina kon niet in onze database gevonden worden",    
    "PAGE_PRIVATE_TOGGLED"			=> "De pagina is nu %m1%",
	"PAGE_ACCESS_REMOVED"			=> "Pagina toegang verwijderd voor %m1% toegangslevel(s)",
	"PAGE_ACCESS_ADDED"			=> "Pagina toegang toegevoegd voor %m1% toegangslevel(s)",
    "ACCESS_DENIED" => "Hmm, het lijtk erop dat je geen toestemming hebt om deze pagina te bekijken.",
    "LOGIN_REQUIRED" => "Sorry, je moet ingelogt zijn om deze pagina te kunnen zien.",
	));

//Permissions
$lang = array_merge($lang,array(
    "GROUP_INVALID_ID"              => "De opgevraagde groep id doet het niet",
	"PERMISSION_CHAR_LIMIT"			=> "Toegangslevel naam moet tussen de %m1% en %m2% tekens bevatten.",
	"PERMISSION_NAME_IN_USE"		=> "Toegangslevel naam %m1% wordt al gebruikt",
	"PERMISSION_DELETION_SUCCESSFUL_NAME"		=> "Met succes toegangslevel '%m1%' verwijderd",
    "PERMISSION_DELETIONS_SUCCESSFUL"	=> "Met succes toegangslevel %m1% verwijderd",
	"PERMISSION_CREATION_SUCCESSFUL"	=> "Met succes toegangslevel `%m1%` aangemaakt",
	"GROUP_UPDATE"		=> "Groep `%m1%` met succes geupdate.",
	"PERMISSION_REMOVE_PAGES"		=> "Met succes toegang tot %m1% pagina(s) verwijderd",
	"PERMISSION_ADD_PAGES"			=> "Met succes toegang tot %m1% pagina(s) toegevoegd",
	"PERMISSION_REMOVE_USERS"		=> "Met succes %m1% gebruiker(s) verwijderd",
	"PERMISSION_ADD_USERS"			=> "Met succes %m1% gebruiker(s) toegevoegd",
	"CANNOT_DELETE_PERMISSION_GROUP" => "Je kan niet de groep '%m1%' verwijderen",
	));

//Private Messages
$lang = array_merge($lang,array(
    "PM_RECEIVER_DELETION_SUCCESSFUL"   => "Bericht verwijderd",
));
?>
