<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * German message token translations for the 'admin' sprinkle.
 *
 * @author X-Anonymous-Y
 * @author kevinrombach
 * @author splitt3r
 * @author Le-Morri
 */
return [
    'ACTIVITY' => [
        1      => 'Aktivität',
        2      => 'Aktivitäten',
        'LAST' => 'Letzte Aktivität',
        'PAGE' => 'Eine Auflistung der Benutzeraktivitäten',
        'TIME' => 'Aktivitätszeit',
    ],
    'CACHE' => [
        'CLEAR'             => 'Cache löschen',
        'CLEAR_CONFIRM'     => 'Sind Sie sicher, dass Sie den Seiten-Cache löschen möchten?',
        'CLEAR_CONFIRM_YES' => 'Ja, Cache löschen',
        'CLEARED'           => 'Cache wurde erfolgreich gelöscht!',
    ],
    'DASHBOARD'           => 'Übersicht',
    'NO_FEATURES_YET'     => 'Es sieht aus, als wären für Ihren Account noch keine Funktionen aktiviert... bisher. Entweder sie wurden bisher noch nicht implementiert, oder Ihnen fehlen noch die Berechtigungen. Trotzdem ist es schön, dass Sie auf unsere Seite gekommen sind!',
    'DELETE_MASTER'       => 'Sie können das Root-Konto nicht löschen!',
    'DELETION_SUCCESSFUL' => 'Benutzer <strong>{{user_name}}</strong> wurde erfolgreich gelöscht.',
    'DETAILS_UPDATED'     => 'Konto-Daten für <strong>{{user_name}}</strong> aktualisiert.',
    'DISABLE_MASTER'      => 'Sie können das Root-Konto nicht deaktivieren!',
    'DISABLE_SELF'        => 'Sie können Ihr eigenes Konto nicht deaktivieren!',
    'DISABLE_SUCCESSFUL'  => 'Konto von <strong>{{user_name}}</strong> wurde erfolgreich deaktiviert.',
    'ENABLE_SUCCESSFUL'   => 'Konto von {{user_name}} wurde erfolgreich aktiviert.',
    'GROUP'               => [
        1                     => 'Gruppe',
        2                     => 'Gruppen',
        'CREATE'              => 'Gruppe erstellen',
        'CREATION_SUCCESSFUL' => 'Die Gruppe <strong>{{name}}</strong> wurde erfolgreich erstellt',
        'DELETE'              => 'Gruppe löschen',
        'DELETE_CONFIRM'      => 'Möchten Sie die Gruppe <strong>{{name}}</strong> wirklich löschen?',
        'DELETE_DEFAULT'      => 'Sie können die Gruppe <strong>{{name}}</strong> nicht löschen, da es die Standardgruppe für neu registrierte Benutzer ist.',
        'DELETE_YES'          => 'Ja, Gruppe löschen',
        'DELETION_SUCCESSFUL' => 'Die Gruppe <strong>{{name}}</strong> wurde erfolgreich gelöscht',
        'EDIT'                => 'Gruppe bearbeiten',
        'ICON'                => 'Gruppensymbol',
        'ICON_EXPLAIN'        => 'Symbol für Gruppenmitglieder',
        'INFO_PAGE'           => 'Gruppeninformationsseite für {{name}}',
        'MANAGE'              => 'Gruppe verwalten',
        'NAME'                => 'Gruppenname',
        'NAME_EXPLAIN'        => 'Geben Sie einen Namen für die Gruppe ein',
        'NOT_EMPTY'           => 'Sie können das nicht tun, denn es sind noch Benutzer mit der Gruppe <strong>{{name}}</strong> verbunden.',
        'PAGE_DESCRIPTION'    => 'Eine Liste der Gruppen für Ihre Website. Bietet Verwaltungstools für das Bearbeiten und Löschen von Gruppen.',
        'SUMMARY'             => 'Gruppen Zusammenfassung',
        'UPDATE'              => 'Details für die Gruppe <strong>{{name}}</strong> aktualisiert',
    ],
    'MANUALLY_ACTIVATED'    => '{{user_name}}\'s Konto wurde manuell aktiviert.',
    'MASTER_ACCOUNT_EXISTS' => 'Das Root-Konto existiert bereits!',
    'MIGRATION'             => [
        'REQUIRED' => 'Datenbankaktualisierung erforderlich',
    ],
    'PERMISSION' => [
        1                  => 'Berechtigung',
        2                  => 'Berechtigungen',
        'ASSIGN_NEW'       => 'Neue Berechtigung zuweisen',
        'HOOK_CONDITION'   => 'Haken/Bedingungen',
        'ID'               => 'Berechtigungs-ID',
        'INFO_PAGE'        => 'Berechtigungs Informationen für \'{{name}}\'',
        'MANAGE'           => 'Berechtigungen verwalten',
        'NOTE_READ_ONLY'   => '<strong>Bitte beachten Sie:</strong> Berechtigungen werden als "Teil des Quelltexts" gesehen und können hier nicht bearbeitet werden. Um Berechtigungen hinzuzufügen, zu bearbeiten, oder zu löschen, benutzen Sie bitte folgende Dokumentation zur <a href="https://learn.userfrosting.com/database/extending-the-database" target="about:_blank">Datenbank Migration.</a>',
        'PAGE_DESCRIPTION' => 'Eine Liste der Berechtigungen für Ihre Website. Bietet Verwaltungstools zum Bearbeiten und Löschen von Berechtigungen.',
        'SUMMARY'          => 'Berechtigungs Zusammenfassung',
        'UPDATE'           => 'Berechtigungen aktualisieren',
        'VIA_ROLES'        => 'Besitzt die Berechtigung durch die Rolle',
    ],
    'ROLE' => [
        1                     => 'Rolle',
        2                     => 'Rollen',
        'ASSIGN_NEW'          => 'Neue Rolle zuweisen',
        'CREATE'              => 'Rolle erstellen',
        'CREATION_SUCCESSFUL' => 'Die Rolle <strong>{{name}}</strong> wurde erfolgreich erstellt',
        'DELETE'              => 'Rolle löschen',
        'DELETE_CONFIRM'      => 'Sind Sie sicher, dass Sie die Rolle <strong>{{name}}</strong> löschen möchten?',
        'DELETE_DEFAULT'      => 'Sie können die Rolle <strong>{{name}}</strong> nicht löschen, da es eine Standardrolle für neu registrierte Benutzer ist.',
        'DELETE_YES'          => 'Ja, Rolle löschen',
        'DELETION_SUCCESSFUL' => 'Die Rolle <strong>{{name}}</strong> wurde erfolgreich gelöscht',
        'EDIT'                => 'Rolle bearbeiten',
        'HAS_USERS'           => 'Sie können das nicht machen weil es noch Benutzer gibt, die die Rolle <strong>{{name}}</strong> haben.',
        'INFO_PAGE'           => 'Rolleninformationsseite für {{name}}',
        'MANAGE'              => 'Rollen verwalten',
        'NAME'                => 'Name',
        'NAME_EXPLAIN'        => 'Geben Sie einen Namen für die Rolle ein',
        'NAME_IN_USE'         => 'Eine Rolle mit dem Namen <strong>{{name}}</strong> existiert bereits',
        'PAGE_DESCRIPTION'    => 'Eine Liste der Rollen für Ihre Website. Bietet Verwaltungstools zum Bearbeiten und Löschen von Rollen.',
        'PERMISSIONS_UPDATED' => 'Berechtigungen für die Rolle <strong>{{name}}</strong> aktualisiert',
        'SUMMARY'             => 'Rollen Zusammenfassung',
        'UPDATED'             => 'Rollen aktualisieren',
    ],
    'SYSTEM_INFO' => [
        '@TRANSLATION' => 'System Information',
        'DB_NAME'      => 'Name der Datenbank',
        'DB_VERSION'   => 'Datenbankversion',
        'DIRECTORY'    => 'Projektverzeichnis',
        'PHP_VERSION'  => 'PHP-Version',
        'SERVER'       => 'Web-Server-Software',
        'SPRINKLES'    => 'Geladene Sprinkles',
        'UF_VERSION'   => 'UserFrosting Version',
        'URL'          => 'Website-Stamm-Url',
    ],
    'TOGGLE_COLUMNS' => 'Spalten anpassen',
    'USER'           => [
        1       => 'Benutzer',
        2       => 'Benutzer',
        'ADMIN' => [
            'CHANGE_PASSWORD'    => 'Benutzerpasswort ändern',
            'SEND_PASSWORD_LINK' => 'Senden Sie dem Benutzer einen Link, der ihnen erlaubt, ihr eigenes Passwort zu wählen',
            'SET_PASSWORD'       => 'Setzen Sie das Passwort des Benutzers als',
        ],
        'ACTIVATE'         => 'Benutzer aktivieren',
        'CREATE'           => 'Benutzer erstellen',
        'CREATED'          => 'Benutzer <strong>{{user_name}}</strong> wurde erfolgreich erstellt',
        'DELETE'           => 'Benutzer löschen',
        'DELETE_CONFIRM'   => 'Sind Sie sicher, dass Sie den Benutzer <strong>{{name}}</strong> löschen möchten?',
        'DELETE_YES'       => 'Ja, Benutzer löschen',
        'DELETED'          => 'Benutzer gelöscht',
        'DISABLE'          => 'Benutzer deaktivieren',
        'EDIT'             => 'Benutzer bearbeiten',
        'ENABLE'           => 'Benutzer aktivieren',
        'INFO_PAGE'        => 'Benutzerinformationsseite für {{name}}',
        'LATEST'           => 'Neueste Benutzer',
        'PAGE_DESCRIPTION' => 'Eine Liste der Benutzer für Ihre Website. Bietet Management-Tools, einschließlich der Möglichkeit, Benutzerdaten bearbeiten, manuell aktivieren, Benutzer aktivieren/deaktivieren, und vieles mehr.',
        'SUMMARY'          => 'Benutzer Zusammenfassung',
        'VIEW_ALL'         => 'Alle Benutzer anzeigen',
        'WITH_PERMISSION'  => 'Benutzer mit dieser Berechtigung',
    ],
    'X_USER' => [
        0 => 'Keine Benutzer',
        1 => '{{plural}} Benutzer',
        2 => '{{plural}} Benutzer',
    ],
];
