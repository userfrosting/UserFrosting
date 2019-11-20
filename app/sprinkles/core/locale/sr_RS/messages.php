<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Serbian message token translations for the 'core' sprinkle.
 *
 * @author zbigcheese https://github.com/zbigcheese
 */
return [
    '@PLURAL_RULE' => 1,

    'ABOUT' => 'O nama',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Captcha kod',
        'FAIL'         => 'Niste ispravno uneli captcha kod.',
        'SPECIFY'      => 'Unesite captcha kod',
        'VERIFY'       => 'Verifikujte captcha kod',
    ],

    'CSRF_MISSING' => 'CSRF token nije prisutan. Probajte da učitate stranicu ponovo?',

    'DB_INVALID'    => 'Ne mogu da se povežem sa bazom podataka. Ukoliko ste Vi administrator, molimo pogledajte error log.',
    'DESCRIPTION'   => 'Opis',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Preuzmi',
        'CSV'          => 'Preuzmi CSV',
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Vaša email adresa',
    ],

    'HOME'  => 'Početna',

    'LEGAL' => [
        '@TRANSLATION' => 'Pravne odredbe',
        'DESCRIPTION'  => 'Naše pravne odredbe se odnose na korišćenje ovog sajta i njegovih servisa.',
    ],

    'LOCALE' => [
        '@TRANSLATION' => 'Lokalitet',
    ],

    'NAME'       => 'Naziv',
    'NAVIGATION' => 'Navigacija',
    'NO_RESULTS' => 'Žap nam je, ništa nije pronađeno.',

    'PAGINATION' => [
        'GOTO' => 'Idi na stranicu',
        'SHOW' => 'Prikaži',

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'   => '{startRow} do {endRow} od {filteredRows} ({totalRows})',
        'NEXT'     => 'Sledeća stranica',
        'PREVIOUS' => 'Prethodna stranica',
        'FIRST'    => 'Prva stranica',
        'LAST'     => 'Zadnja stranica',
    ],
    'PRIVACY' => [
        '@TRANSLATION' => 'Politika privatnosti',
        'DESCRIPTION'  => 'Naša politika privatnosti prikazuje koje vrste informacija prikupljamo i kako one mogu biti korišćene.',
    ],

    'SLUG'           => 'Slug',
    'SLUG_CONDITION' => 'Slug/Uslovi',
    'SLUG_IN_USE'    => '<strong>{{slug}}</strong> slug već postoji',
    'STATUS'         => 'Status',
    'SUGGEST'        => 'Predloži',

    'UNKNOWN' => 'Nepoznato',

    // Actions words
    'ACTIONS'                  => 'Akcije',
    'ACTIVATE'                 => 'Aktiviraj',
    'ACTIVE'                   => 'Aktivno',
    'ADD'                      => 'Dodaj',
    'CANCEL'                   => 'Otkaži',
    'CONFIRM'                  => 'Potvrdi',
    'CREATE'                   => 'Kreiraj',
    'DELETE'                   => 'Obriši',
    'DELETE_CONFIRM'           => 'Da li ste sigurni da želite da ovo obrišete?',
    'DELETE_CONFIRM_YES'       => 'Da, obriši',
    'DELETE_CONFIRM_NAMED'     => 'Da li ste sigurni da želite da obrišete {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Da, obriši {{name}}',
    'DELETE_CANNOT_UNDONE'     => 'Ova akcija ne može biti vraćena.',
    'DELETE_NAMED'             => 'Obriši {{name}}',
    'DENY'                     => 'Odbij',
    'DISABLE'                  => 'Onemogući',
    'DISABLED'                 => 'Onemogućeno',
    'EDIT'                     => 'Izmeni',
    'ENABLE'                   => 'Omogući',
    'ENABLED'                  => 'Omogućeno',
    'OVERRIDE'                 => 'Pregazi',
    'RESET'                    => 'Resetuj',
    'SAVE'                     => 'Sačuvaj',
    'SEARCH'                   => 'Pretraži',
    'SORT'                     => 'Sortiraj',
    'SUBMIT'                   => 'Pošalji',
    'PRINT'                    => 'Štampaj',
    'REMOVE'                   => 'Ukloni',
    'UNACTIVATED'              => 'Neaktiviran',
    'UPDATE'                   => 'Osveži',
    'YES'                      => 'Da',
    'NO'                       => 'Ne',
    'OPTIONAL'                 => 'Opciono',

    // Misc.
    'BUILT_WITH_UF'     => 'Napravljeno sa <a href="http://www.userfrosting.com">UserFrosting-om</a>',
    'ADMINLTE_THEME_BY' => 'Temu napravio <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> Sva prava zadržana',
    'WELCOME_TO'        => 'Dobrodočšli na {{title}}!',
];
