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
$lang = array();
// Installazione
$lang = array_merge($lang,array(
"INSTALLER_INCOMPLETE" => "Non puoi creare l'account root finchè l'installazione non è conclusa!",
"MASTER_ACCOUNT_EXISTS" => "L'account primario esiste già!",
"MASTER_ACCOUNT_NOT_EXISTS" => "Non puoi registrare un account finche l'account primario non sarà creato!",
"CONFIG_TOKEN_MISMATCH" => "Il Token di configurazione è errato"
));
//Account
$lang = array_merge($lang,array(
"ACCOUNT_SPECIFY_USERNAME" => "Inserisci il tuo username",
"ACCOUNT_SPECIFY_PASSWORD" => "Inserisci la tua password",
"ACCOUNT_SPECIFY_EMAIL"	=> "Inserisci il tuo indirizzo E-mail",
"ACCOUNT_INVALID_EMAIL"	=> "Indirizzo mail non valido",
"ACCOUNT_INVALID_USER_ID"	=> "User ID richiesto non è valido",
"ACCOUNT_INVALID_PAY_TYPE"	=> "Metodo di pagamento non valido. Il tipo di pagamento deve essere 'una tantum' o 'orario'.",
"ACCOUNT_USER_OR_EMAIL_INVALID"	=> "L'indirizzo mail o il nome utente non sono validi",
"ACCOUNT_USER_OR_PASS_INVALID"	=> "Il nome utente o la password non sono validi",
"ACCOUNT_ALREADY_ACTIVE"	=> "Il tuo account è già attivato",
"ACCOUNT_REGISTRATION_DISABLED"	=> "La registrazione di nuovi account è stata bloccata",
"ACCOUNT_INACTIVE"	=> "Il tuo account non è stato attivato. Controlla nella tua mail ( anche nella cartella dello spam ) per riceve le instruzioni per attivare il tuo account",
"ACCOUNT_DISABLED"	=> "Questo account è stato disattivato, contattaci per maggiori informazioni",
"ACCOUNT_USER_CHAR_LIMIT"	=> "Il tuo username deve essere tra i %m1% e i %m2% caratteri",
"ACCOUNT_DISPLAY_CHAR_LIMIT"	=> "Il tuo nome visualizzato deve essere tra i %m1% e i %m2% caratteri",
"ACCOUNT_PASS_CHAR_LIMIT"	=> "La tua password deve essere tra i %m1% e i %m2% caratteri",
"ACCOUNT_TITLE_CHAR_LIMIT"	=> "Il titolo utente deve essere tra i %m1% e i %m2% caratteri",
"ACCOUNT_PASS_MISMATCH"	=> "I due campi devono combaciare",
"ACCOUNT_DISPLAY_INVALID_CHARACTERS"	=> "Il nome visualizzato può contenere solo caratteri alfanumerici",
"ACCOUNT_USERNAME_IN_USE"	=> "Il nome utente '%m1%' è già in uso",
"ACCOUNT_DISPLAYNAME_IN_USE"	=> "Il nome visualizzato '%m1%' è già in uso",
"ACCOUNT_EMAIL_IN_USE"	=> "L'email %m1% è già in uso",
"ACCOUNT_LINK_ALREADY_SENT"	=> "Una mail di attivazione è già stata mandata al tuo indirizzo email %m1% ora/ore fa",
"ACCOUNT_NEW_ACTIVATION_SENT"	=> "Ti è stato inviato un nuovo codice di attivazione, controlla la tua email",
"ACCOUNT_SPECIFY_NEW_PASSWORD"	=> "Inserisci la tua nuova password",
"ACCOUNT_SPECIFY_CONFIRM_PASSWORD"	=> "Conferma la tua nuova password",
"ACCOUNT_NEW_PASSWORD_LENGTH"	=> "La nuova password deve essere tra %m1% e i %m2% caratteri",
"ACCOUNT_PASSWORD_INVALID"	=> "La password corrente non corrisponde con quella in memoria",
"ACCOUNT_DETAILS_UPDATED"	=> "Detagli dell'account aggiornati",
"ACCOUNT_ACTIVATION_MESSAGE"	=> "Devi attivare il tuo account prima di poter accedere. Segui il link sottostante per poter attivare l'account. \n\n
%m1%activate_user.php?token=%m2%",
"ACCOUNT_CREATION_COMPLETE"	=> "Account per l'utente %m1% è stato creato.",
"ACCOUNT_ACTIVATION_COMPLETE"	=> "Hai attivato il tuo account con successo, ora puoi eseguire il login.",
"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "Sei stato registrato con successo ora puoi eseguire il login",
"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "Sei stato registrato con successo. Riceverai presto una mail per l'attivazione. Devi attivare il tuo account prima di eseguire il login.",
"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE"	=> "Non puoi aggiornare con la stessa password",
"ACCOUNT_PASSWORD_UPDATED"	=> "Password aggiornata",
"ACCOUNT_EMAIL_UPDATED"	=> "Email aggiornata",
"ACCOUNT_TOKEN_NOT_FOUND"	=> "Il token non esiste / l'account è già stato attivato",
"ACCOUNT_USER_INVALID_CHARACTERS"	=> "L'username può essere composto da caratteri alfanumerici",
"ACCOUNT_DELETE_MASTER" => "Non puoi eliminare l'account principale!",
"ACCOUNT_DISABLE_MASTER" => "Non puoi disattivare l'account principale!",
"ACCOUNT_DISABLE_SUCCESSFUL" => "Account disattivato con successo!",
"ACCOUNT_ENABLE_SUCCESSFUL" => "Account attivato con successo.",
"ACCOUNT_DELETIONS_SUCCESSFUL"	=> "Hai eliminato %m1% utenti",
"ACCOUNT_MANUALLY_ACTIVATED"	=> "%m1% è stato attivato manualmente",
"ACCOUNT_DISPLAYNAME_UPDATED"	=> "Nome visualizzato cambiato in %m1%",
"ACCOUNT_TITLE_UPDATED"	=> "%m1% titolo utente cambiato in %m2%",
"ACCOUNT_GROUP_ADDED"	=> "Aggiunto utente al gruppo %m1%.",
"ACCOUNT_GROUP_REMOVED"	=> "Utente rimosso dal gruppo %m1%.",
"ACCOUNT_GROUP_NOT_MEMBER"	=> "L'utente non è membro del gruppo %m1%.",
"ACCOUNT_GROUP_ALREADY_MEMBER"	=> "L'utente è già membro del gruppo %m1%.",
"ACCOUNT_INVALID_USERNAME"	=> "Nome utente non valido",
"ACCOUNT_PRIMARY_GROUP_SET" => "Configurato il gruppo primario con successo.",
));
//Configurazione
$lang = array_merge($lang,array(
"CONFIG_NAME_CHAR_LIMIT"	=> "Nome del sito deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_URL_CHAR_LIMIT"	=> "URL del sito deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_EMAIL_CHAR_LIMIT"	=> "Email del sito deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_TITLE_CHAR_LIMIT"	=> "Nuovo titolo utente deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_ACTIVATION_TRUE_FALSE"	=> "Attivazione con mail deve essere 'vero' o 'falso'",
"CONFIG_REGISTRATION_TRUE_FALSE"	=> "Registrazione utente deve essere 'vero' o 'falso'",
"CONFIG_ACTIVATION_RESEND_RANGE"	=> "Soglia di attivazione deve essere compresa tra %m1% e tra %m2% ore",
"CONFIG_LANGUAGE_CHAR_LIMIT"	=> "Percorso delle traduzioni deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_LANGUAGE_INVALID"	=> "Non vi è alcun file per la chiave della lingua `%m1%`",
"CONFIG_TEMPLATE_CHAR_LIMIT"	=> "Percorso del modello deve essere compreso tra %m1% e tra %m2% caratteri",
"CONFIG_TEMPLATE_INVALID"	=> "Non vi è alcun file per la chiave del modello `%m1%`",
"CONFIG_EMAIL_INVALID"	=> "L'email che hai inserito non è valida",
"CONFIG_INVALID_URL_END"	=> "Si prega di inserire il finale / nella URL del vostro sito",
"CONFIG_UPDATE_SUCCESSFUL"	=> "La configurazione del tuo sito è stata aggiornata. Potrebbe essere necessario caricare una nuova pagina per tutte le impostazioni abbiano effetto",
));
//Recupero password
$lang = array_merge($lang,array(
"FORGOTPASS_INVALID_TOKEN"	=> "Il tuo token di attivazione non è valido",
"FORGOTPASS_OLD_TOKEN" => "Il token di accesso è scaduto",
"FORGOTPASS_COULD_NOT_UPDATE" => "Password non aggiornata",
"FORGOTPASS_NEW_PASS_EMAIL"	=> "E' stata inviata una mail con la nuova password",
"FORGOTPASS_REQUEST_CANNED"	=> "Richiesta di recupero password cancellata",
"FORGOTPASS_REQUEST_EXISTS"	=> "C'è già una richiesta di recupero password pendente per questo account",
"FORGOTPASS_REQUEST_SUCCESS"	=> "Ti sono state inviate per mail le istruzioni per riprendere possesso del tuo account",
));
//Email
$lang = array_merge($lang,array(
"MAIL_ERROR"	=> "Errore nell'invio della mail, contatta l'amministratore di sistema",
"MAIL_TEMPLATE_BUILD_ERROR"	=> "Errore creando il template della mail",
"MAIL_TEMPLATE_DIRECTORY_ERROR"	=> "Impossibile aprire la directory 'mail-templates'. Prova a impostare la directory mail %m1%",
"MAIL_TEMPLATE_FILE_EMPTY"	=> "Il file contenente i template è vuoto, nulla da inviare",
));
//Miscellaneous
$lang = array_merge($lang,array(
"PASSWORD_HASH_FAILED" => "Hash della password fallito. Contatta l'amministratore di sistema.",
"NO_DATA"	=> "Nessun dato inviato",
"CAPTCHA_FAIL"	=> "Domanda di sicurezza sbagliata",
"CONFIRM"	=> "Conferma",
"DENY"	=> "Nega",
"SUCCESS"	=> "Successo",
"ERROR"	=> "Errore",
"NOTHING_TO_UPDATE"	=> "Niente da aggiornare",
"SQL_ERROR"	=> "Errore SQL fatale",
"FEATURE_DISABLED"	=> "Funzione attualmente disattivata",
"PAGE_INVALID_ID" => "La pagina con questo id non esiste",
"PAGE_PRIVATE_TOGGLED"	=> "Questa pagina è ora %m1%",
"PAGE_ACCESS_REMOVED"	=> "Accesso alla pagina rimosso dal %m1% livello/livelli di permesso",
"PAGE_ACCESS_ADDED"	=> "Accesso alla pagina aggiunto dal %m1% livello/livelli di permesso",
"ACCESS_DENIED" => "Sembra tu non abbiamo il permesso di fare questo.",
));
//Permessi
$lang = array_merge($lang,array(
"GROUP_INVALID_ID" => "Il gruppo richiesto non esiste",
"PERMISSION_CHAR_LIMIT"	=> "Il nome del permesso deve essere tra %m1% e tra %m2% caratteri",
"PERMISSION_NAME_IN_USE"	=> "Permesso con nome '%m1%' già in uso",
"PERMISSION_DELETION_SUCCESSFUL_NAME"	=> "Eliminato il permesso '%m1%'",
"PERMISSION_DELETIONS_SUCCESSFUL"	=> "Eliminato %m1% permesso di livello",
"PERMISSION_CREATION_SUCCESSFUL"	=> "Creato con successo il livello di permesso `%m1%`",
"GROUP_UPDATE"	=> "Gruppo `%m1%` aggiornato.",
"PERMISSION_REMOVE_PAGES"	=> "Rimossa/rimosse con successo %m1% pagina/pagine",
"PERMISSION_ADD_PAGES"	=> "Aggiunta/aggiunte con successo %m1% pagina/pagine",
"PERMISSION_REMOVE_USERS"	=> "Rimosso/rimossi con successo %m1% utente/utenti",
"PERMISSION_ADD_USERS"	=> "Aggiunto/aggiunti con successo %m1% utente/utenti",
"CANNOT_DELETE_PERMISSION_GROUP" => "Mom puoi eliminare il gruppo '%m1%'",
));
//Messaggi privati
$lang = array_merge($lang,array(
"PM_RECEIVER_DELETION_SUCCESSFUL"	=> "Messaggio eliminato",
));
?>
