<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Italian message token translations for the 'admin' sprinkle.
 * This translation was generated with Google translate.  Please contribute if you are a native speaker.
 *
 * @author Alexander Weissman
 * @author Pietro Marangon (@Pe46dro)
 */
return [
    'ACTIVITY' => [
        1      => 'Attività',
        2      => 'Attività',
        'LAST' => 'Ultima attività',
        'PAGE' => 'Un elenco delle attività degli utenti',
        'TIME' => 'Tempo di attività',
    ],
    'CACHE' => [
        'CLEAR'             => 'Elimina cache',
        'CLEAR_CONFIRM'     => 'Sei sicuro di voler eliminare la cache del sito?',
        'CLEAR_CONFIRM_YES' => 'Sì, elimina la cache',
        'CLEARED'           => 'La cache è stata eliminata correttamente!',
    ],
    'DASHBOARD'           => 'Pannello di Controllo',
    'NO_FEATURES_YET'     => 'Sembra che nessuna funzione sia stata creata per questo account... ancora. Forse non sono ancora state implementate o qualcuno ha dimenticato di dartene accesso. In entrambi i casi, siamo contenti di averti qui!',
    'DELETE_MASTER'       => 'Non puoi eliminare l\'account principale!',
    'DELETION_SUCCESSFUL' => 'Hai eliminato l\'utente utente <strong>{{user_name}}</strong>.',
    'DETAILS_UPDATED'     => 'Dettagli account aggiornati per l\'utente <strong>{{user_name}}</strong>',
    'DISABLE_MASTER'      => 'Non puoi disattivare l\'account principale!',
    'DISABLE_SELF'        => 'Non puoi disattivare il tuo account!',
    'DISABLE_SUCCESSFUL'  => 'Account dell\'utente <strong>{{user_name}}</strong> disattivato con successo!',
    'ENABLE_SUCCESSFUL'   => 'Account dell\'utente <strong>{{user_name}}</strong> attivato con successo.',
    'GROUP'               => [
        1                     => 'Gruppo',
        2                     => 'Gruppi',
        'CREATE'              => 'Crea un gruppo',
        'CREATION_SUCCESSFUL' => 'Gruppo <strong>{{name}}</strong> creato con successo',
        'DELETE'              => 'Elimina gruppo',
        'DELETE_CONFIRM'      => 'Sei sicuro di voler eliminare il gruppo <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Non puoi eliminare il gruppo <strong>{{name}}</strong> perché è il gruppo predefinito per gli utenti appena registrati.',
        'DELETE_YES'          => 'Sì, elimina il gruppo',
        'DELETION_SUCCESSFUL' => 'Gruppo <strong>{{name}}</strong> eliminato con successo',
        'EDIT'                => 'Modifica gruppo',
        'ICON'                => 'Icona del gruppo',
        'ICON_EXPLAIN'        => 'Icona per i membri del gruppo',
        'INFO_PAGE'           => 'Pagina informazioni del Gruppo per <strong>{{name}}</strong>',
        'MANAGE'              => 'Gestisci gruppo',
        'NAME'                => 'Nome gruppo',
        'NAME_EXPLAIN'        => 'Inserisci un nome per il gruppo',
        'NOT_EMPTY'           => 'Non puoi farlo perché ci sono ancora utenti associati al gruppo <strong>{{name}}</strong>.',
        'PAGE_DESCRIPTION'    => 'Un elenco dei gruppi per il tuo sito. Fornisce strumenti di gestione per la modifica e l\'eliminazione di gruppi.',
        'SUMMARY'             => 'Riepilogo Gruppo',
        'UPDATE'              => 'Dettagli del gruppo  <strong>{{name}}</strong> aggiornati.',
    ],
    'MANUALLY_ACTIVATED'    => '<strong>{{user_name}}</strong> è stato attivato manualmente',
    'MASTER_ACCOUNT_EXISTS' => 'L\'account primario esiste già!',
    'MIGRATION'             => [
        'REQUIRED' => 'È necessario aggiornare il database',
    ],
    'PERMISSION' => [
        1                  => 'Permesso',
        2                  => 'Permessi',
        'ASSIGN_NEW'       => 'Assegna nuovo permesso',
        'HOOK_CONDITION'   => 'Hook/Condizioni',
        'ID'               => 'ID permesso',
        'INFO_PAGE'        => 'Pagina di informazioni sulle autorizzazioni per <strong>{{name}}</strong>',
        'MANAGE'           => 'Gestione delle autorizzazioni',
        'NOTE_READ_ONLY'   => '<strong>Nota:</strong> le autorizzazioni sono considerate "parte del codice" e non possono essere modificate tramite l\'interfaccia. Per aggiungere, rimuovere o modificare le autorizzazioni, i gestori del sito devono utilizzare <a href="https://learn.userfrosting.com/database/extending-the-database" target="about: _blank ">migrazione del database.</a>',
        'PAGE_DESCRIPTION' => 'Un elenco delle autorizzazioni per il tuo sito. Fornisce strumenti di gestione per la modifica e l\'eliminazione delle autorizzazioni.',
        'SUMMARY'          => 'Sommario autorizzazioni',
        'UPDATE'           => 'Aggiorna autorizzazioni',
        'VIA_ROLES'        => 'Ha i permessi tramite i ruoli',
    ],
    'ROLE' => [
        1                     => 'Ruolo',
        2                     => 'Ruoli',
        'ASSIGN_NEW'          => 'Assegna nuovo ruolo',
        'CREATE'              => 'Crea ruolo',
        'CREATION_SUCCESSFUL' => 'Creato con successo il ruolo <strong>{{name}}</strong>',
        'DELETE'              => 'Elimina ruolo',
        'DELETE_CONFIRM'      => 'Sei sicuro di voler eliminare il ruolo <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Non puoi eliminare il ruolo <strong>{{name}}</strong> perché è un ruolo predefinito per gli utenti appena registrati.',
        'DELETE_YES'          => 'Sì, elimina il ruolo',
        'DELETION_SUCCESSFUL' => 'Ruolo <strong>{{name}}</strong> eliminato',
        'EDIT'                => 'Modifica ruolo',
        'HAS_USERS'           => 'Non puoi farlo perché ci sono ancora utenti che hanno il ruolo <strong>{{name}}</strong>.',
        'INFO_PAGE'           => 'Pagina di informazioni sui ruoli per <strong>{{name}}</strong>',
        'MANAGE'              => 'Gestisci Ruoli',
        'NAME'                => 'Nome',
        'NAME_EXPLAIN'        => 'Inserisci un nome per il ruolo',
        'NAME_IN_USE'         => 'Esiste già un ruolo denominato <strong>{{name}}</strong>',
        'PAGE_DESCRIPTION'    => 'Un elenco dei ruoli per il tuo sito. Fornisce strumenti di gestione per la modifica e l\'eliminazione di ruoli.',
        'PERMISSIONS_UPDATED' => 'Autorizzazioni aggiornate per il ruolo <strong>{{name}}</strong>',
        'SUMMARY'             => 'Riepilogo dei Ruoli',
        'UPDATED'             => 'Dettagli aggiornati per il ruolo <strong>{{name}}</strong>',
    ],
    'SYSTEM_INFO' => [
        '@TRANSLATION' => 'Informazioni sul sistema',
        'DB_NAME'      => 'Nome del database',
        'DB_VERSION'   => 'Versione del database',
        'DIRECTORY'    => 'Directory del progetto',
        'PHP_VERSION'  => 'Versione PHP',
        'SERVER'       => 'Software Webserver',
        'SPRINKLES'    => 'Sprinkles caricati',
        'UF_VERSION'   => 'Versione UserFrosting',
        'URL'          => 'Url della root del sito',
    ],
    'TOGGLE_COLUMNS' => 'Scambia le colonne',
    'USER'           => [
        1       => 'Utente',
        2       => 'Utenti',
        'ADMIN' => [
            'CHANGE_PASSWORD'    => 'Cambia password utente',
            'SEND_PASSWORD_LINK' => 'Invia all\'utente un link che gli permetterà di scegliere una nuova password.',
            'SET_PASSWORD'       => 'Impostare la password dell\'utente come',
        ],
        'ACTIVATE'         => 'Attiva utente',
        'CREATE'           => 'Creare utente',
        'CREATED'          => 'Account utente <strong>{{user_name}}</strong> creato.',
        'DELETE'           => 'Elimina utente',
        'DELETE_CONFIRM'   => 'Sei sicuro di voler eliminare l\'utente <strong>{{name}}</strong>?',
        'DELETE_YES'       => 'Sì, elimina l\'utente',
        // 'DELETED'          => 'User deleted',
        'DISABLE'          => 'Disabilita l\'utente',
        'EDIT'             => 'Modifica utente',
        'ENABLE'           => 'Abilita utente',
        'INFO_PAGE'        => 'Pagina informazioni utente per <strong>{{name}}</strong>',
        'LATEST'           => 'Ultimi Utenti',
        'PAGE_DESCRIPTION' => 'Un elenco degli utenti del tuo sito. Fornisce strumenti di gestione, tra cui la possibilità di modificare i dettagli utente, attivare manualmente gli utenti, abilitare / disabilitare gli utenti e altro ancora.',
        'SUMMARY'          => 'Riepilogo account',
        'VIEW_ALL'         => 'Visualizza tutti gli utenti',
        'WITH_PERMISSION'  => 'Utenti con questa autorizzazione',
    ],
    'X_USER' => [
        0 => 'Nessun utente',
        1 => '{{plural}} utente',
        2 => '{{plural}} utenti',
    ],
];
