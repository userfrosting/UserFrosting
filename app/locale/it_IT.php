<?php

/**
 * it_IT
 *
 * Italian message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n 
 * @author Pietro Marangon (@Pe46dro)
 */

/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "La registrazione è semplice e veloce",
	"MENU_USERS" => "Utenti",
	"MENU_CONFIGURATION" => "Configurazione",
	"MENU_SITE_SETTINGS" => "Configurazione Sito",
	"MENU_GROUPS" => "Gruppi",
	"HEADER_MESSAGE_ROOT" => "LOGGATO COME ROOT"
]);

// Installazione
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "Non puoi creare l'account root finchè l'installazione non è conclusa!",
	"MASTER_ACCOUNT_EXISTS" => "L'account primario esiste già!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "Non puoi registrare un account finche l'account primario non sarà creato!",
	"CONFIG_TOKEN_MISMATCH" => "Il Token di configurazione è errato"
));

// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Inserisci il tuo username",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Inserire il nome visualizzato",
	"ACCOUNT_SPECIFY_PASSWORD" => "Inserisci la tua password",
	"ACCOUNT_SPECIFY_EMAIL" => "Inserisci il tuo indirizzo E-mail",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Inserire il captcha",
	"ACCOUNT_SPECIFY_LOCALE" => "Specificare una località valida.",
	"ACCOUNT_INVALID_EMAIL" => "Indirizzo mail non valido",
	"ACCOUNT_INVALID_USERNAME" => "Username non valido",
	"ACCOUNT_INVALID_USER_ID" => "User ID richiesto non è valido",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "L'indirizzo mail o il nome utente non sono validi",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Il nome utente o la password non sono validi",
	"ACCOUNT_ALREADY_ACTIVE" => "Il tuo account è già attivato",
	"ACCOUNT_REGISTRATION_DISABLED" => "La registrazione di nuovi account è stata bloccata",
	"ACCOUNT_REGISTRATION_LOGOUT" => "Non è possibile registrare un account mentre si è loggati",
	"ACCOUNT_INACTIVE" => "Il tuo account non è stato attivato. Controlla nella tua mail ( anche nella cartella dello spam ) per riceve le instruzioni per attivare il tuo account",
	"ACCOUNT_DISABLED" => "Questo account è stato disattivato, contattaci per maggiori informazioni",
	"ACCOUNT_USER_CHAR_LIMIT" => "Il tuo username deve essere tra i {{min}} e i {{max}} caratteri",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Il tuo nome visualizzato deve essere tra i {{min}} e i {{max}} caratteri",
	"ACCOUNT_PASS_CHAR_LIMIT" => "La tua password deve essere tra i {{min}} e i {{max}} caratteri",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "L'email deve essere di almeno {{min}} e massimo {{max}} caratteri.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Il titolo utente deve essere tra i {{min}} e i {{max}} caratteri",
	"ACCOUNT_PASS_MISMATCH" => "I due campi devono combaciare",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Il nome visualizzato può contenere solo caratteri alfanumerici",
	"ACCOUNT_USERNAME_IN_USE" => "Il nome utente '{{user_name}}' è già in uso",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Il nome visualizzato '{{display_name}}' è già in uso",
	"ACCOUNT_EMAIL_IN_USE" => "L'email '{{email}}' è già in uso",
	"ACCOUNT_LINK_ALREADY_SENT" => "Una mail di attivazione è già stata mandata al tuo indirizzo email {{resend_activation_threshold}} ora/ore fa",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Ti è stato inviato un nuovo codice di attivazione, controlla la tua email",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Inserisci la tua nuova password",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Conferma la tua nuova password",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "La nuova password deve essere tra {{min}} e i {{max}} caratteri",
	"ACCOUNT_PASSWORD_INVALID" => "La password corrente non corrisponde con quella in memoria",
	"ACCOUNT_DETAILS_UPDATED" => "Detagli dell'account aggiornati",
	"ACCOUNT_CREATION_COMPLETE" => "Account per l'utente '{{user_name}}' è stato creato.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Hai attivato il tuo account con successo, ora puoi eseguire il login.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Sei stato registrato con successo ora puoi eseguire il login",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Sei stato registrato con successo. Riceverai presto una mail per l'attivazione. Devi attivare il tuo account prima di eseguire il login.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Non puoi aggiornare con la stessa password",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Conferma la password attuale",
	"ACCOUNT_SETTINGS_UPDATED" => "Impostazioni account aggiornate",
	"ACCOUNT_PASSWORD_UPDATED" => "Password aggiornata",
	"ACCOUNT_EMAIL_UPDATED" => "Email aggiornata",
	"ACCOUNT_TOKEN_NOT_FOUND" => "Il token non esiste / l'account è già stato attivato",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "L'username può essere composto da caratteri alfanumerici",
	"ACCOUNT_DELETE_MASTER" => "Non puoi eliminare l'account principale!",
	"ACCOUNT_DISABLE_MASTER" => "Non puoi disattivare l'account principale!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "Account disattivato con successo!",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "Account attivato con successo.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "Hai eliminato utente '{{user_name}}'.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "'{{user_name}}' è stato attivato manualmente",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "Nome visualizzato cambiato in '{{display_name}}'",
	"ACCOUNT_TITLE_UPDATED" => "{{user_name}} titolo utente cambiato in '{{title}}'",
	"ACCOUNT_GROUP_ADDED" => "Aggiunto utente al gruppo '{{name}}'.",
	"ACCOUNT_GROUP_REMOVED" => "Utente rimosso dal gruppo '{{name}}'.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "L'utente non è membro del gruppo '{{name}}'.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "L'utente è già membro del gruppo '{{name}}'.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Gruppo primario impostato con successo per '{{user_name}}'.",
	"ACCOUNT_WELCOME" => "Bentornato, {{display_name}}"
));

// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "Il campo '{{self}}' deve essere specificato.",
	"VALIDATE_BOOLEAN" => "Il valore per '{{self}}' deve essere '0' o '1'.",
	"VALIDATE_INTEGER" => "Il valore per '{{self}}' deve essere un intero.",
	"VALIDATE_ARRAY" => "I valori per '{{self}}' devono essere in un vettore."
));

// Configurazione
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "Stai provando ad aggiornare le impostazioni per il plugin '{{plugin}}', ma non c'è nessun plugin con quel nome.",
	"CONFIG_SETTING_INVALID" => "Stai provando ad aggionare la configurazione '{{name}}' per il plugin '{{plugin}}', ma non esiste.",
	"CONFIG_NAME_CHAR_LIMIT" => "Nome del sito deve essere compreso tra {{min}} e tra {{max}} caratteri",
	"CONFIG_URL_CHAR_LIMIT" => "URL del sito deve essere compreso tra {{min}} e tra {{max}} caratteri",
	"CONFIG_EMAIL_CHAR_LIMIT" => "Email del sito deve essere compreso tra {{min}} e tra {{max}} caratteri",
	"CONFIG_TITLE_CHAR_LIMIT" => "Nuovo titolo utente deve essere compreso tra {{min}} e tra {{max}} caratteri",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "Attivazione con mail deve essere 'vero' o 'falso'",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "Registrazione utente deve essere 'vero' o 'falso'",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "Soglia di attivazione deve essere compresa tra {{min}} e tra {{max}} ore",
	"CONFIG_EMAIL_INVALID" => "L'email che hai inserito non è valida",
	"CONFIG_UPDATE_SUCCESSFUL" => "La configurazione del tuo sito è stata aggiornata. Potrebbe essere necessario caricare una nuova pagina per tutte le impostazioni abbiano effetto",
	"MINIFICATION_SUCCESS" => "Minificazione e concatenazione di CSS e JS eseguita con successo"
));

// Recupero password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Il tuo token di attivazione non è valido",
	"FORGOTPASS_OLD_TOKEN" => "Il token di accesso è scaduto",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Password non aggiornata",
	"FORGOTPASS_NEW_PASS_EMAIL" => "E' stata inviata una mail con la nuova password",
	"FORGOTPASS_REQUEST_CANNED" => "Richiesta di recupero password cancellata",
	"FORGOTPASS_REQUEST_EXISTS" => "C'è già una richiesta di recupero password pendente per questo account",
	"FORGOTPASS_REQUEST_SUCCESS" => "Ti sono state inviate per mail le istruzioni per riprendere possesso del tuo account"
));

// Email
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "Errore nell'invio della mail, contatta l'amministratore di sistema"
));

// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Hash della password fallito. Contatta l'amministratore di sistema.",
	"NO_DATA" => "Nessun dato inviato",
	"CAPTCHA_FAIL" => "Domanda di sicurezza sbagliata",
	"CONFIRM" => "Conferma",
	"DENY" => "Nega",
	"SUCCESS" => "Successo",
	"ERROR" => "Errore",
	"SERVER_ERROR" => "Sembra esserci un errore nel server. Se sei un admin controlla gli errori di PHP",
	"NOTHING_TO_UPDATE" => "Niente da aggiornare",
	"SQL_ERROR" => "Errore SQL fatale",
	"FEATURE_DISABLED" => "Funzione attualmente disattivata",
	"ACCESS_DENIED" => "Sembra tu non abbiamo il permesso di fare questo.",
	"LOGIN_REQUIRED" => "Devi essere loggato per accedere a questa risorsa",
	"LOGIN_ALREADY_COMPLETE" => "Sei già loggato!"
));

// Permessi
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "Il gruppo richiesto non esiste",
	"GROUP_CHAR_LIMIT" => "Il nome del permesso deve essere tra {{min}} e tra {{max}} caratteri",
	"GROUP_NAME_IN_USE" => "Gruppo con nome '{{name}}' già in uso",
	"GROUP_DELETION_SUCCESSFUL" => "Eliminato il gruppo '{{name}}'",
	"GROUP_CREATION_SUCCESSFUL" => "Creato con successo il livello di gruppo `{{name}}`",
	"GROUP_UPDATE" => "Gruppo `{{name}}` aggiornato.",
	"CANNOT_DELETE_GROUP" => "Mom puoi eliminare il gruppo '{{name}}'",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "Il gruppo '{{name}}' perchè è configurato come gruppo primario per i nuovi utenti. Per favore seleziona prima un nuovo gruppo primario per i nuovi utenti."
));

return $lang;