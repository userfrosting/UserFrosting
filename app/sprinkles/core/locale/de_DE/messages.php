<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * German message token translations for the 'core' sprinkle.
 *
 * @author X-Anonymous-Y
 * @author kevinrombach
 * @author splitt3r
 */
return [
    '@PLURAL_RULE' => 1,

    'ABOUT'                             => 'Über',

    'CAPTCHA' => [
        '@TRANSLATION'                  => 'Sicherheitscode',
        'FAIL'                          => 'Sie haben den Sicherheitscode nicht korrekt eingegeben.',
        'SPECIFY'                       => 'Geben Sie den Sicherheitscode ein',
        'VERIFY'                        => 'Überprüfen Sie den Sicherheitscode'
    ],

    'CSRF_MISSING'                      => 'Fehlender CSRF-Token. Versuchen, die Seite zu aktualisieren und erneut zu senden?',

    'DB_INVALID'                        => 'Keine Verbindung zur Datenbank möglich. Wenn Sie ein Administrator sind, überprüfen Sie bitte Ihr Fehlerprotokoll.',
    'DESCRIPTION'                       => 'Beschreibung',
    'DOWNLOAD'                          => [
        '@TRANSLATION'                  => 'Herunterladen',
        'CSV'                           => 'CSV herunterladen'
    ],

    'EMAIL' => [
        '@TRANSLATION'                  => 'E-Mail',
        'YOUR'                          => 'Ihre E-Mail-Adresse'
    ],

    'HOME'                              => 'Startseite',

    'LEGAL' => [
        '@TRANSLATION'                  => 'Rechtsgrundsatz',
        'DESCRIPTION'                   => 'Unser Rechtsgrundsatz gilt für die Benutzung dieser Internetseite und unserer Dienste.'
    ],

    'LOCALE' => [
        '@TRANSLATION'                  => 'Sprache'
    ],

    'NAME'                              => 'Name',
    'NAVIGATION'                        => 'Navigation',
    'NO_RESULTS'                        => 'Sorry, hier gibt es bisher nichts zu sehen.',

    'PAGINATION' => [
        'GOTO'                          => 'Gehe zu Seite',
        'SHOW'                          => 'Anzeigen',

                                        // Paginator
                                        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
                                        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'                        => '{startRow} bis {endRow} von {filteredRows} ({totalRows})',

        'NEXT'                          => 'Nächste Seite',
        'PREVIOUS'                      => 'Vorherige Seite',
        'FIRST'                         => 'Erste Seite',
        'LAST'                          => 'Letzte Seite'
    ],
    'PRIVACY' => [
        '@TRANSLATION'                  => 'Datenschutzbestimmungen',
        'DESCRIPTION'                   => 'In unsere Datenschutzbestimmungen erklären wir Ihnen, welche Daten wir sammeln und wozu wir diese benutzen.'
    ],

    'SLUG'                              => 'Schnecke',
    'SLUG_CONDITION'                    => 'Schnecke/Bedingungen',
    'SLUG_IN_USE'                       => 'Die Schnecke <strong>{{slug}}</strong> existiert bereits',
    'STATUS'                            => 'Status',
    'SUGGEST'                           => 'Vorschlagen',

    'UNKNOWN'                           => 'Unbekannt',

    // Actions words
    'ACTIONS'                           => 'Aktionen',
    'ACTIVATE'                          => 'Aktivieren',
    'ACTIVE'                            => 'Aktiv',
    'ADD'                               => 'Hinzufügen',
    'CANCEL'                            => 'Abbrechen',
    'CONFIRM'                           => 'Bestätigen',
    'CREATE'                            => 'Erstellen',
    'DELETE'                            => 'Löschen',
    'DELETE_CONFIRM'                    => 'Möchten Sie diese wirklich löschen?',
    'DELETE_CONFIRM_YES'                => 'Ja, löschen',
    'DELETE_CONFIRM_NAMED'              => 'Möchten Sie {{name}} wirklich löschen?',
    'DELETE_CONFIRM_YES_NAMED'          => 'Ja, {{name}} löschen',
    'DELETE_CANNOT_UNDONE'              => 'Diese Aktion kann nicht rückgängig gemacht werden.',
    'DELETE_NAMED'                      => '{{name}} löschen',
    'DENY'                              => 'Verweigern',
    'DISABLE'                           => 'Deaktivieren',
    'DISABLED'                          => 'Deaktiviert',
    'EDIT'                              => 'Bearbeiten',
    'ENABLE'                            => 'Aktivieren',
    'ENABLED'                           => 'Aktiviert',
    'OVERRIDE'                          => 'Überschreiben',
    'RESET'                             => 'Zurücksetzen',
    'SAVE'                              => 'Speichern',
    'SEARCH'                            => 'Suchen',
    'SORT'                              => 'Sortieren',
    'SUBMIT'                            => 'Einreichen',
    'PRINT'                             => 'Drucken',
    'REMOVE'                            => 'Entfernen',
    'UNACTIVATED'                       => 'Unaktiviert',
    'UPDATE'                            => 'Aktualisieren',
    'YES'                               => 'Ja',
    'NO'                                => 'Nein',
    'OPTIONAL'                          => 'Optional',

    // Misc.
    'BUILT_WITH_UF'                     => 'Errichtet mit <a href="http://www.userfrosting.com">UserFrosting</a>',
    'ADMINLTE_THEME_BY'                 => 'Theme von <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> Alle Rechte vorbehalten',
    'WELCOME_TO'                        => 'Willkommen auf {{title}}!'
];
