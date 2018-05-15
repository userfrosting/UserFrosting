<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 *
 * Italian message token translations for the 'account' sprinkle.
 * This translation was generated with Google translate.  Please contribute if you are a native speaker.
 *
 * @package userfrosting\i18n\it
 * @author Alexander Weissman
 * @author Pietro Marangon (@Pe46dro)
 */

return [
    "ACCOUNT" => [
        "@TRANSLATION" => "Account",

        "ACCESS_DENIED" => "Sembra tu non abbia il permesso per effettuare questa azione.",

        "DISABLED" => "Questo account è stato disattivato, contattaci per maggiori informazioni",

        "EMAIL_UPDATED" => "Email aggiornata",

        "INVALID" => "Questo account non esiste. Può essere stato eliminato. Contattaci per ulteriori informazioni.",

        "MASTER_NOT_EXISTS" => "Non puoi registrare un account finché l'account primario non sarà creato!",
        "MY" => "Il mio account",

        "SESSION_COMPROMISED" => [
            "@TRANSLATION"  => "La tua sessione è stata compromessa. Devi eseguire il logout su tutti i dispositivi, quindi accedere nuovamente e assicurarti che i tuoi dati non siano stati manomessi.",
            "TITLE"         => "Il tuo account potrebbe essere stato compromesso",
            "TEXT"          => "Qualcuno potrebbe aver utilizzato le tue informazioni di accesso per accedere a questa pagina. Per la tua sicurezza tutte le sessioni sono state disconnesse. <a href=\"{{url}}\">Accedi</a> e controlla l'account per attività sospette. Potresti anche voler cambiare la password."
        ],
        "SESSION_EXPIRED"       => "La tua sessione è scaduta. Accedi nuovamente.",

        "SETTINGS" => [
            "@TRANSLATION"  => "Impostazioni account",
            "DESCRIPTION"   => "Aggiorna le impostazioni del tuo account, tra cui email, nome e password.",
            "UPDATED"       => "Impostazioni account aggiornate"
        ],

        "TOOLS" => "Strumenti account",

        "UNVERIFIED" => "Il tuo account non è stato attivato. Controlla nella tua mail (anche nella cartella dello spam) per ricevere le instruzioni per attivare il tuo account",

        "VERIFICATION" => [
            "NEW_LINK_SENT"     => "Ti è stato inviato un nuovo codice di attivazione, controlla la tua email ({{email}}).",
            "RESEND"            => "Invia nuovamente email di verifica.",
            "COMPLETE"          => "Hai verificato con successo il tuo account. Puoi ora accedere.",
            "EMAIL"             => "Inserisci l'indirizzo email che hai utilizzato per registrarti e la tua email di verifica sarà inviata nuovamente.",
            "PAGE"              => "Invia nuovamente l'email di verifica per il tuo nuovo account.",
            "SEND"              => "Invia il collegamento di verifica per il mio account",
            "TOKEN_NOT_FOUND"   => "Il token non esiste / l'account è già stato attivato"
        ]
    ],

    "EMAIL" => [
        "INVALID"               => "Non esiste alcun account per <strong>{{email}}</strong>.",
        "IN_USE"                => "L'email '{{email}}' è già in uso",
        "VERIFICATION_REQUIRED" => "Email (verifica richiesta - utilizza un indirizzo reale!)"
    ],

    "EMAIL_OR_USERNAME" => "Nome utente o Indirizzo Email",

    "FIRST_NAME" => "Nome",

    "HEADER_MESSAGE_ROOT" => "ACCESSO ROOT",

    "LAST_NAME" => "Cognome",
    "LOCALE" => [
        "ACCOUNT" => "La lingua da utilizzare per il tuo account",
        "INVALID" => "<strong>{{locale}}</strong> non è una lingua valida.",
        
        
    ],
    "LOGIN" => [
        "@TRANSLATION"      => "Accesso",
        "ALREADY_COMPLETE"  => "Hai già eseguito l'accesso!",
        "SOCIAL"            => "O accedi con",
        "REQUIRED"          => "Devi eseguire l'accesso per accedere a questa risorsa"
    ],
    "LOGOUT" => "Esci",

    "NAME" => "Nome",

    "NAME_AND_EMAIL" => "Nome e email",

    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION"   => "Accedi al tuo account {{site_name}} o iscriviti per un nuovo account.",
            "SUBTITLE"      => "Registrati gratuitamente o accedi con un account esistente.",
            "TITLE"         => "Iniziamo!",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION" => "Password",

        "BETWEEN"   => "La password deve essere tra {{min}} e i {{max}} caratteri",

        "CONFIRM"               => "Conferma la password",
        "CONFIRM_CURRENT"       => "Conferma la password attuale",
        "CONFIRM_NEW"           => "Conferma la tua nuova password",
        "CONFIRM_NEW_EXPLAIN"   => "Inserisci nuovamente la nuova password",
        "CONFIRM_NEW_HELP"      => "Richiesto solo se si seleziona una nuova password",
        "CREATE" => [
            "@TRANSLATION"  => "Crea password",
            "PAGE"          => "Scegli una password per il tuo nuovo account.",
            "SET"           => "Imposta password e accedi"
        ],
        "CURRENT"               => "Password attuale",
        "CURRENT_EXPLAIN"       => "Devi confermare la tua password corrente per apportare modifiche",

        "FORGOTTEN" => "Password dimenticata",
        "FORGET" => [
            "@TRANSLATION" => "Ho dimenticato la mia password",

            "COULD_NOT_UPDATE"  => "Password non aggiornata",
            "EMAIL"             => "Inserisci l'indirizzo email che hai utilizzato per iscriverti. Un link con le istruzioni per reimpostare la tua password verrà inviata via email.",
            "EMAIL_SEND"        => "Invia email per il reset della password",
            "INVALID"           => "Questa richiesta di ripristino della password non è stata trovata o è scaduta.  Prova a <a href=\"{{url}}\">reinviare</a> la tua richiesta.",
            "PAGE"              => "Ottieni un collegamento per reimpostare la tua password.",
            "REQUEST_CANNED"    => "Richiesta di recupero password annullata.",
            "REQUEST_SENT"      => "Se l'email <strong>{{email}}</strong> corrisponde a un account, verrà inviato un collegamento per la reimpostazione della password a <strong>{{email}}</strong>."
        ],

        "HASH_FAILED"       => "Hash della password fallito. Contatta l'amministratore di sistema.",
        "INVALID"           => "La password corrente non corrisponde con quella attuale",
        "NEW"               => "Nuova Password",
        "NOTHING_TO_UPDATE" => "Non puoi impostare la stessa password precedente",

        "RESET" => [
            "@TRANSLATION"      => "Reimposta la Password",
            "CHOOSE"            => "Inserisci la tua nuova password",
            "PAGE"              => "Scegli una nuova password per il tuo account.",
            "SEND"              => "Imposta nuova password e accedi"
        ],

        "UPDATED"           => "Password aggiornata"
    ],

    "PROFILE"       => [
        "SETTINGS"  => "Impostazioni profilo",
        "UPDATED"   => "Impostazioni profilo aggiornate"
    ],

    "RATE_LIMIT_EXCEEDED"       => "Il limite di esecuzioni per questa azione è stato superato. Devi aspettare altri {{delay}} secondi prima che tu possa fare un altro tentativo.",
    "REGISTER"      => "Registrati",
    "REGISTER_ME"   => "Iscrivimi",
    "REGISTRATION" => [
        "BROKEN"            => "Siamo spiacenti, c'è un problema con il nostro processo di registrazione dell'account. Vi preghiamo di contattarci direttamente per assistenza.",
        "COMPLETE_TYPE1"    => "Registrazione effettuata con successo. Ora puoi eseguire il login",
        "COMPLETE_TYPE2"    => "Registrazione effettuata con successo. Riceverai presto una mail a <strong>{{email}}</strong> per l'attivazione. Devi attivare il tuo account prima di eseguire il login.",
        "DISABLED"          => "La registrazione di nuovi account è limitata",
        "LOGOUT"            => "Non è possibile registrare un account mentre hai eseguito l'accesso ad un altro account",
        "WELCOME"           => "La registrazione è semplice e veloce"
    ],
    "REMEMBER_ME"               => "Ricordami",
    "REMEMBER_ME_ON_COMPUTER"   => "Ricordami su questo dispositivo (non consigliato per i computer pubblici)",

    "SIGN_IN_HERE"          => "Hai già un account? <a href=\"{{url}}\">Accedi qui</a>",
    "SIGNIN"                => "Accedi",
    "SIGNIN_OR_REGISTER"    => "Accedi o iscriviti",
    "SIGNUP"                => "Registrazione",

    "TOS"           => "Termini e condizioni",
    "TOS_AGREEMENT" => "Registrando un account su {{site_title}}, accetti i <a {{link_attributes | raw}}>Termini e le Condizioni</a>.",
    "TOS_FOR"       => "Termini e condizioni di {{title}}",

    "USERNAME" => [
        "@TRANSLATION" => "Nome utente",

        "CHOOSE"        => "Inserisci il tuo nome utente",
        "INVALID"       => "Nome utente non valido",
        "IN_USE"        => "Il nome utente '{{user_name}}' è già in uso",
        "NOT_AVAILABLE" => "Il nome utente <strong>{{user_name}}</strong> non è disponibile. Scegli un nome diverso, oppure fai clic su \"suggerisci\"."
    ],

    "USER_ID_INVALID"       => "Questo ID utente non esiste",
    "USER_OR_EMAIL_INVALID" => "L'indirizzo mail o il nome utente non sono validi",
    "USER_OR_PASS_INVALID"  => "Il nome utente o la password non sono validi",

    "WELCOME" => "Bentornato, {{display_name}}"
];
