<?php

/**
 * nl_NL
 *
 * Dutch message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n 
 * @author Joey (@joey102)
 * @author Andrew (@editandrew)
 */

/*
{{name}} - Dynamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Installer
$lang = array_merge($lang,array(
    "INSTALLER_INCOMPLETE" => "Je kan geen master account aanmaken tot dat de installatie afgerond is.",
    "MASTER_ACCOUNT_EXISTS" => "Er is al een master account aangemaakt.",
    "MASTER_ACCOUNT_NOT_EXISTS" => "Je kan geen account aanmaken tot er een master account is.",
    "CONFIG_TOKEN_MISMATCH" => "Sorry, je configuratie token is niet geldig."
));

// Account
$lang = array_merge($lang,array(
    "ACCOUNT_SPECIFY_USERNAME" => "Voer je gebruikersnaam in",
    "ACCOUNT_SPECIFY_PASSWORD" => "Voer je wachtwoord in",
    "ACCOUNT_SPECIFY_EMAIL" => "Voer je emailadres in",
    "ACCOUNT_INVALID_EMAIL" => "Ongeldig emailadres",
    "ACCOUNT_INVALID_USER_ID" => "De opgevraagde id bestaat niet.",
    "ACCOUNT_USER_OR_EMAIL_INVALID" => "Gebruikersnaam of emailadres niet correct",
    "ACCOUNT_USER_OR_PASS_INVALID" => "Gebruikersnaam of wachtwoord niet correct",
    "ACCOUNT_ALREADY_ACTIVE" => "Je account is al geactiveerd",
    "ACCOUNT_REGISTRATION_DISABLED" => "Het registreren van nieuwe accounts is op dit moment uitgeschakeld.",
    "ACCOUNT_INACTIVE" => "Je account is niet actief. Controleer je email / spam voor de activatie instructies.",
    "ACCOUNT_DISABLED" => "Deze account is gedeactiveerd. Neem contact met ons op voor meer informatie.",
    "ACCOUNT_USER_CHAR_LIMIT" => "Je gebruikersnaam moet tussen de {{min}} en {{max}} tekens zijn.",
    "ACCOUNT_DISPLAY_CHAR_LIMIT" => "Je weergegeven naam moet tussen de {{min}} en {{max}} tekens bevatten.",
    "ACCOUNT_PASS_CHAR_LIMIT" => "Je wachtwoord moet tussen de {{min}} en {{max}} tekens bevatten.",
    "ACCOUNT_TITLE_CHAR_LIMIT" => "Titels moeten tussen de {{min}} en {{max}} tekens bevatten.",
    "ACCOUNT_PASS_MISMATCH" => "Je wachtwoord komt niet overeen.",
    "ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "De weergegeven naam kan enkel alpha-numeric karakters bevatten.",
    "ACCOUNT_USERNAME_IN_USE" => "De gebruikersnaam '{{user_name}}' is al in gebruik",
    "ACCOUNT_DISPLAYNAME_IN_USE" => "De weergegeven naam '{{display_name}}' is al in gebruik.",
    "ACCOUNT_EMAIL_IN_USE" => "De email '{{email}}' is al in gebruik.",
    "ACCOUNT_LINK_ALREADY_SENT" => "Een activatie email is al verzonden naar dit emailadres in de afgelopen {{resend_activation_threshold}} seconden. Heb alstublieft geduld.",
    "ACCOUNT_NEW_ACTIVATION_SENT" => "We hebben je een nieuwe activatie email verzonden. Controleer je email.",
    "ACCOUNT_SPECIFY_NEW_PASSWORD" => "Voer een nieuw wachtwoord in",
    "ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Bevestig het nieuwe wachtwoord",
    "ACCOUNT_NEW_PASSWORD_LENGTH" => "Nieuwe wachtwoord moet tussen de {{min}} en {{max}} tekens bevatten.",
    "ACCOUNT_PASSWORD_INVALID" => "Het wachtwoord komt niet overeen met het wachtwoord dat bij ons bekend staat.",
    "ACCOUNT_DETAILS_UPDATED" => "Account details bijgewerkt.",
    "ACCOUNT_CREATION_COMPLETE" => "Account voor nieuwe gebruiker '{{user_name}}' is met succes aangemaakt.",
    "ACCOUNT_ACTIVATION_COMPLETE" => "Je hebt je account geactiveerd. Je kan nu inloggen!",
    "ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Je hebt met succes je account geregistreerd. Je kan nu inloggen.",
    "ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Je hebt met succes je account geregistreerd. Je ontvangt nu een activeringsmail. Je account moet geactiveerd worden voordat je kan inloggen.",
    "ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Je kan je account niet aanpassen naar hetzelfde wachtwoord.",
    "ACCOUNT_PASSWORD_UPDATED" => "Wachtwoord aangepast.",
    "ACCOUNT_EMAIL_UPDATED" => "Account email aangepast.",
    "ACCOUNT_TOKEN_NOT_FOUND" => "Token bestaat niet / Account is al actief.",
    "ACCOUNT_USER_INVALID_CHARACTERS" => "Gebruikersnaam kan alleen alfanumerieke karakters bevatten.",
    "ACCOUNT_DELETE_MASTER" => "Je kan de master account niet verwijderen!",
    "ACCOUNT_DISABLE_MASTER" => "Je kan de master account niet uitzetten!",
    "ACCOUNT_DISABLE_SUCCESSFUL" => "Account is met succes uitgezet.",
    "ACCOUNT_ENABLE_SUCCESSFUL" => "Account is met succes aangezet.",
    "ACCOUNT_DELETION_SUCCESSFUL" => "Gebruiker '{{user_name}}' verwijderd",
    "ACCOUNT_MANUALLY_ACTIVATED" => "{{user_name}}'s account handmatig geactiveerd",
    "ACCOUNT_DISPLAYNAME_UPDATED" => "Weergegeven naam aangepast naar '{{display_name}}'.",
    "ACCOUNT_TITLE_UPDATED" => "{{user_name}}'s titel is aangepast naar '{{title}}'.",
    "ACCOUNT_GROUP_ADDED" => "Gebruiker toegevoegd aan de groep '{{name}}'.",
    "ACCOUNT_GROUP_REMOVED" => "Gebruiker verwijderd van de groep '{{name}}'.",
    "ACCOUNT_GROUP_NOT_MEMBER" => "Gebruiker is geen lid van de groep '{{name}}'.",
    "ACCOUNT_GROUP_ALREADY_MEMBER" => "Gebruiker is al een lid van de groep '{{name}}'.",
    "ACCOUNT_INVALID_USERNAME" => "Ongeldig gebruikersnaam",
    "ACCOUNT_PRIMARY_GROUP_SET" => "De primaire groep van de gebruiker is met succes ingesteld."
));

// Configuration
$lang = array_merge($lang,array(
    "CONFIG_NAME_CHAR_LIMIT" => "Site naam moet tussen de {{min}} en {{max}} tekens bevatten",
    "CONFIG_URL_CHAR_LIMIT" => "Site url moet tussen de {{min}} en {{max}} tekens bevatten",
    "CONFIG_EMAIL_CHAR_LIMIT" => "Site email moet tussen de {{min}} en {{max}} tekens bevatten",
    "CONFIG_TITLE_CHAR_LIMIT" => "Nieuwe gebruikerstitel moet tussen de {{min}} en {{max}} tekens bevatten",
    "CONFIG_ACTIVATION_TRUE_FALSE" => "Email activeren moet true of false zijn",
    "CONFIG_REGISTRATION_TRUE_FALSE" => "Gebruikersregistratie moet true of false zijn",
    "CONFIG_ACTIVATION_RESEND_RANGE" => "Tijdslimiet op activatie moet tussen de {{min}} en {{max}} uur zijn",
    "CONFIG_EMAIL_INVALID" => "Het ingevulde emailadres is niet geldig",
    "CONFIG_UPDATE_SUCCESSFUL" => "Je site configuratie is met succes aangepast. Vergeet niet de pagina te herladen om de wijzigingen te zijn."
));

// Forgot Password
$lang = array_merge($lang,array(
    "FORGOTPASS_INVALID_TOKEN" => "Je activeringstoken is niet meer geldig.",
    "FORGOTPASS_OLD_TOKEN" => "Token is verlopen.",
    "FORGOTPASS_COULD_NOT_UPDATE" => "Kon wachtwoord niet updaten.",
    "FORGOTPASS_NEW_PASS_EMAIL" => "We hebben je een nieuw wachtwoord opgestuurd per email.",
    "FORGOTPASS_REQUEST_CANNED" => "Wachtwoordverzoek geannuleerd.",
    "FORGOTPASS_REQUEST_EXISTS" => "Er loopt al een wachtwoordverzoek voor dit account.",
    "FORGOTPASS_REQUEST_SUCCESS" => "We hebben instructies toegestuurd om toegang tot je account terug te krijgen."
));

// Mail
$lang = array_merge($lang,array(
    "MAIL_ERROR" => "Fatale fout bij het versturen van email. Neem contact op met de server administrator.",
));

// Miscellaneous
$lang = array_merge($lang,array(
    "PASSWORD_HASH_FAILED" => "Encryptie wachtwoord mislukt. Neem contact op met de website administrator.",
    "NO_DATA" => "Geen of ongeldige data verzonden.",
    "CAPTCHA_FAIL" => "CAPTCHA mislukt.",
    "CONFIRM" => "Bevestig",
    "DENY" => "Annuleer",
    "SUCCESS" => "Succes",
    "ERROR" => "Fout",
    "NOTHING_TO_UPDATE" => "Niets te updaten",
    "SQL_ERROR" => "Fatale SQL fout",
    "FEATURE_DISABLED" => "Deze functie is uitgeschakeld.",
    "ACCESS_DENIED" => "Hmm, het lijkt erop dat je geen toestemming hebt om deze pagina te bekijken.",
    "LOGIN_REQUIRED" => "Sorry, je moet ingelogd zijn om de pagina te kunnen zien."
));

// Permissions
$lang = array_merge($lang,array(
    "GROUP_INVALID_ID" => "De opgevraagde groep id doet het niet",
    "GROUP_CHAR_LIMIT" => "Groep naam moet tussen de {{min}} en {{max}} tekens bevatten.",
    "GROUP_NAME_IN_USE" => "Groep naam '{{name}}' wordt al gebruikt",
    "GROUP_DELETION_SUCCESSFUL" => "Met succes groep '{{name}}' verwijderd",
    "GROUP_CREATION_SUCCESSFUL" => "Met succes groep '{{name}}' aangemaakt",
    "GROUP_UPDATE" => "Groep '{{name}}' met succes geupdate.",
    "CANNOT_DELETE_GROUP" => "Je kan de groep '{{name}}' niet verwijderen"
));

return $lang;