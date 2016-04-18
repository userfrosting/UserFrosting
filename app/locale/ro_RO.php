<?php

/**
 * ro_RO
 *
 * Traducerea mesajelor in Romana
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "Inregistrarea este simpla si rapida.",
	"MENU_USERS" => "Utilizatori",
	"MENU_CONFIGURATION" => "Configuratie",
	"MENU_SITE_SETTINGS" => "Setarile siteului",
	"MENU_GROUPS" => "Grupuri",
	"HEADER_MESSAGE_ROOT" => "ESTI AUTENTIFICAT CA ADMINISTRATOR"
]);

// Installer
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "Nu poti inregistra contul de administrator pana nu termini instalatia cu succes!",
	"MASTER_ACCOUNT_EXISTS" => "Contul de administrator exista deja!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "Nu pot inregistra un cont pana nu realizezi contul de administrator!",
	"CONFIG_TOKEN_MISMATCH" => "Tokenul de configuratie nu este corect."
));

// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Te rog sa introduci numele tau de utilizator.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Te rog sa introduci numele de afisare.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Te rog sa introduci parola ta.",
	"ACCOUNT_SPECIFY_EMAIL" => "Te rog sa introduci adresa ta de email.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Te rog sa introduci codul de verificare.",
	"ACCOUNT_SPECIFY_LOCALE" => "Te rog sa introduci o locatie valida",
	"ACCOUNT_INVALID_EMAIL" => "Adresa de email este invalida",
	"ACCOUNT_INVALID_USERNAME" => "Numele de utilizator este invalid",
	"ACCOUNT_INVALID_USER_ID" => "Id-ul specificat nu exista.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Numele de utilizator sau adresa de email este invalida.",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Numele de utilizator sau parola este invalida.",
	"ACCOUNT_ALREADY_ACTIVE" => "Contul tau este deja activat.",
	"ACCOUNT_REGISTRATION_DISABLED" => "Ne pare rau, inregistrarile au fost activate.",
    "ACCOUNT_REGISTRATION_BROKEN" => "Ne pare rau, este o problema cu inregistrarea contului. Te rugam sa ne contactezi direct pentru asistenta",
	"ACCOUNT_REGISTRATION_LOGOUT" => "Ne pare rau, nu poti sa inregistrezi un cont nou in timp ce esti autentificat. Te rugam sa te deconnectezi",
	"ACCOUNT_INACTIVE" => "Contul tau nu este activat. Verifica emailul tau si folderul de spam pentru instructiunile de activare.",
	"ACCOUNT_DISABLED" => "Acest cont a fost dezactivat. Contacteaza-ne pentru mai multe informatii.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Numele de utilizator trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Numele de afisare trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "Parola ta trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "Emailul trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Titlurile trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_PASS_MISMATCH" => "Parola ta si confirmarea acesteia nu se potrviesc.",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Numele de afisare trebuie sa contina doar caractere afla-numerice.",
	"ACCOUNT_USERNAME_IN_USE" => "Utilizatorul '{{user_name}}' este deja folosit.",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Numele de afisare '{{display_name}}' este deja folosit.",
	"ACCOUNT_EMAIL_IN_USE" => "Emailul '{{email}}' este deja folosit.",
	"ACCOUNT_LINK_ALREADY_SENT" => "Un email de activare a fost trimis deja catre aceasta adresa de email in ultimele {{resend_activation_threshold}} secunde. Te rugam sa incerci mai tarziu.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Ti-am trimis un email cu un nou link de activare, te rugam sa verifici.",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Te rugam sa introduci noua ta parola.",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Te rugam sa confirmi noua ta parola.",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "Noua parola trebuie sa fie intre {{min}} si {{max}} caractere.",
	"ACCOUNT_PASSWORD_INVALID" => "Parola ta actuala nu se potriveste cu ce avem noi.",
	"ACCOUNT_DETAILS_UPDATED" => "Dertaliile au fost modificate pentru utilizatorul '{{user_name}}'",
	"ACCOUNT_CREATION_COMPLETE" => "Noul cont de utilizator '{{user_name}}' a fost realizat.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Ti-ai activat contul cu succes. Acum te poti autentifica.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Te-ai inregistrat cu succes. Acum te poti autentifica.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Te-ai inregistrat cu success. Vei primi un email pentru activare. Trebuie sa-ti activezi contul pentru a te autentifica.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Nu poti schimba parola cu aceiasi parola",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Te rugam sa confirmi parola",
	"ACCOUNT_SETTINGS_UPDATED" => "Setarile contului au fost modificate",
	"ACCOUNT_PASSWORD_UPDATED" => "Parola contului a fost modificata",
	"ACCOUNT_EMAIL_UPDATED" => "Adresa de email a contului a fost modificata",
	"ACCOUNT_TOKEN_NOT_FOUND" => "Tokenul nu exista / Contul a fost deja activat",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "Numele de utilizator poate contine doar caractere alfa-numerice",
	"ACCOUNT_DELETE_MASTER" => "Nu poti sterge contul de administrator!",
	"ACCOUNT_DISABLE_MASTER" => "Nu poti dezactiva contul de administrator!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "Contul utilizatorului '{{user_name}}' a fost dezactivat.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "Contul utilizatorului '{{user_name}}' a fost activat cu success.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "Utilizatorul '{{user_name}}' a fost sters cu succes.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "Utilizatorul {{user_name}}' a fost activat manual",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "Numele de afisare pentru {{user_name}} a fost schimbat in '{{display_name}}'",
	"ACCOUNT_TITLE_UPDATED" => "Titlul utilizatorului {{user_name}}' s-a schimbat in '{{title}}'",
	"ACCOUNT_GROUP_ADDED" => "Utilizatorul a fost adaugat grupului '{{name}}'.",
	"ACCOUNT_GROUP_REMOVED" => "Utilizatorul a fost scos din grupul '{{name}}'.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "Utilizatorul nu este membru al grupului '{{name}}'.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "Utilizatorul este deja membru al grupului '{{name}}'.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Grupul principal a fost stabilit pentru '{{user_name}}'.",
	"ACCOUNT_WELCOME" => "Bine ai revenit, {{display_name}}"
));

// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "Campul '{{self}}' trebuie specificata.",
	"VALIDATE_BOOLEAN" => "Valoarea pentru '{{self}}' trebuie sa fie '0' sau '1'.",
	"VALIDATE_INTEGER" => "Valoarea pentru '{{self}}' trebuie sa fie o numar integru.",
	"VALIDATE_ARRAY" => "Valorile pentru '{{self}}' trebuie sa fie o matrice (array)."
));

// Configuration
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "Incerci sa modifici setarile pentru pluginul '{{plugin}}', dar nu este nici un plugin cu numele asta.",
	"CONFIG_SETTING_INVALID" => "Incerci sa modifici setarea '{{name}}' pentru pluginul '{{plugin}}', dar aceasta nu exista.",
	"CONFIG_NAME_CHAR_LIMIT" => "Numele siteului trebuie sa fie intre {{min}} si {{max}} caractere",
	"CONFIG_URL_CHAR_LIMIT" => "Url-ul siteului trebuie sa fie intre {{min}} si {{max}} caractere",
	"CONFIG_EMAIL_CHAR_LIMIT" => "Emailul siteului trebuie sa fie intre {{min}} si {{max}} caractere",
	"CONFIG_TITLE_CHAR_LIMIT" => "Titlul pentru noul utilizator trebuie sa fie intre {{min}} si {{max}} caractere",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "Activarea emailului trebuie sa fie `true` sau `false`",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "Inregistrarea utilizatorului trebuie sa fie `true` sau `false`",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "Perioada activarii trebuie sa fie intre {{min}} si {{max}} ore",
	"CONFIG_EMAIL_INVALID" => "Adresa de email introdusa este invalida",
	"CONFIG_UPDATE_SUCCESSFUL" => "Configuratia siteului tau a fost modificata. Este posibil sa deschizi o pagina noua, pentru ca toate satarile sa intre in efect",
	"MINIFICATION_SUCCESS" => "Fisierele CSS si JS au fost micsorate si concatenate cu succes pentru toate paginile grupurilor."
));

// Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Tokenul tau secret este invalid",
	"FORGOTPASS_OLD_TOKEN" => "Tokenul tau a expirat",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Nu s-a putut modifica parola",
	"FORGOTPASS_REQUEST_CANNED" => "Resetarea parolei uitate a fost anulata",
	"FORGOTPASS_REQUEST_EXISTS" => "A fost deja o cerere de resetare a parolei pentru acest cont",
	"FORGOTPASS_REQUEST_SENT" => "Un link de resetare a parolei a fost trimis la emailul utilizatorului '{{user_name}}'",     
	"FORGOTPASS_REQUEST_SUCCESS" => "Ti-am trimis un email cu instructiunile pentru redobandirea accesului la contul tau"   
));

// Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "O eroare fatala la trimiterea mailului. Va rugam contactati un administrator",
));

// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Encriptarea parolei a esuat. Va rugam contactati un administrator al siteului.",
	"NO_DATA" => "Informatie eronata / Nici o informatie trimisa",
	"CAPTCHA_FAIL" => "Codul de securitate invalid",
	"CONFIRM" => "Confirma",
	"DENY" => "Revoca",
	"SUCCESS" => "Succes",
	"ERROR" => "Eroare",
	"SERVER_ERROR" => "Ups. Se pare ca serverul a dat-o-n bara. Daca esti administrator verifica, te rog erorile din PHP LOG.",
	"NOTHING_TO_UPDATE" => "Nimic de modificat",
	"SQL_ERROR" => "Eroare fatala de SQL",
	"FEATURE_DISABLED" => "Aceasta optiune este momentan dezactivata",
	"ACCESS_DENIED" => "Hmm, se pare ca nu ai permisiuni sa faci asta.",
	"LOGIN_REQUIRED" => "Ne pare rau, trebuie sa fi autentificat pentru a avea acces aici.",
	"LOGIN_ALREADY_COMPLETE" => "Esti deja autentificat!"
));

// Permissions
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "ID-ul grupului nu exista",
	"GROUP_NAME_CHAR_LIMIT" => "Numele grupurilor trebuie sa fie intre {{min}} si {{max}} caractere",
    "AUTH_HOOK_CHAR_LIMIT" => "Carligul de autentificare trebuie sa fie intre {{min}} si {{max}} caractere",
	"GROUP_NAME_IN_USE" => "Numele grupului '{{name}}' este deja folosit",
	"GROUP_DELETION_SUCCESSFUL" => "S-a sters grupul cu succes '{{name}}'",
	"GROUP_CREATION_SUCCESSFUL" => "S-a realizat grupul '{{name}}'",
	"GROUP_UPDATE" => "Detaliile grupului '{{name}}' au fost modificate.",
	"CANNOT_DELETE_GROUP" => "Grupul '{{name}}' nu poate fi sters.",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "Grupul '{{name}}' nu poate fi sters pentru ca este principalul grup pentru noii utilizatori. Te rugam sa selectezi un grup primar diferit.",
    "GROUP_AUTH_EXISTS" => "Grupul '{{name}}' are deja o regula definita pentru carligul '{{hook}}'.",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "Regula carligului '{{hook}}' a fost creeata cu succes pentru grupul '{{name}}'.",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "Regula ce ofera acces grupei '{{name}}' pentru carligul '{{hook}}' a fost modificata.",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "Regula ce ofera acces grupei '{{name}}' pentru carligul '{{hook}}' a fost stearsa.",
    "GROUP_DEFAULT_PRIMARY_NOT_DEFINED" => "Nu poti realiza utlilizatori noi deaorece nu exista o grupa primara principala. Te rugam sa verifici setarile."
));

return $lang;
