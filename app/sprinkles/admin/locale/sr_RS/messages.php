<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Serbian message token translations for the 'admin' sprinkle.
 *
 * @author zbigcheese https://github.com/zbigcheese
 */
return [
    'ACTIVITY' => [
        1 => 'Aktivnost',
        2 => 'Aktivnosti',

        'LAST' => 'Poslednja aktivnost',
        'PAGE' => 'Listing aktivnosti korisnika',
        'TIME' => 'Vreme aktivnosti',
    ],

    'CACHE' => [
        'CLEAR'             => 'Očisti cache aplikacije',
        'CLEAR_CONFIRM'     => 'Da li ste sigurni da želite da očistite cache aplikacije?',
        'CLEAR_CONFIRM_YES' => 'Da, očisti cache',
        'CLEARED'           => 'Cache očišćen uspešno!',
    ],

    'DASHBOARD'             => 'Kontrolna tabla',
    'NO_FEATURES_YET'       => 'Izgleda da ne postoje mogućnosti koje su omogućene za ovaj nalog... još. Moguće da nisu još implementirane ili je neko zaboravio da Vam omogući pristup. U svakom slučaju, drago nam je da ste se pridružili!',
    'DELETE_MASTER'         => 'Ne možete obrisati glavni nalog!',
    'DELETION_SUCCESSFUL'   => 'Korisnik <strong>{{user_name}}</strong> je uspešno obrisan.',
    'DETAILS_UPDATED'       => 'Detalji naloga <strong>{{user_name}}</strong> su uspešno izmenjeni',
    'DISABLE_MASTER'        => 'Ne možete onemogućiti glavni nalog!',
    'DISABLE_SELF'          => 'Ne možete onemogućiti sopstveni nalog!',
    'DISABLE_SUCCESSFUL'    => 'Nalog korisnika <strong>{{user_name}}</strong> je uspešno onemogućen.',

    'ENABLE_SUCCESSFUL' => 'Nalog korisnika <strong>{{user_name}}</strong> je uspešno omogućen.',

    'GROUP' => [
        1 => 'Grupa',
        2 => 'Grupe',

        'CREATE'              => 'Napravi grupu',
        'CREATION_SUCCESSFUL' => 'Uspešno ste napravili grupu <strong>{{name}}</strong>',
        'DELETE'              => 'Obriši grupu',
        'DELETE_CONFIRM'      => 'Da li ste sigurni da želite da obrišete grupu <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Ne možete obrisati grupu <strong>{{name}}</strong> pošto je to podrazumevana grupa za nove korisnike.',
        'DELETE_YES'          => 'Da, obriši grupu',
        'DELETION_SUCCESSFUL' => 'Uspešno ste obrisali grupu <strong>{{name}}</strong>',
        'EDIT'                => 'Izmeni grupu',
        'ICON'                => 'Ikonica grupe',
        'ICON_EXPLAIN'        => 'Ikonica članova grupe',
        'INFO_PAGE'           => 'Stranica informacija za grupu {{name}}',
        'MANAGE'              => 'Upravljaj grupom',
        'NAME'                => 'Ime grupe',
        'NAME_EXPLAIN'        => 'Molimo unesite naziv grupe',
        'NONE'                => 'Bez grupe',
        'NOT_EMPTY'           => 'Akcija nije moguća pošto i dalje postoje korisnici koji pripadaju grupi <strong>{{name}}</strong>.',
        'PAGE_DESCRIPTION'    => 'Listing grupa za ovaj sajt. Stranica pruža alate potrebne za upravljanje, kreiranje i brisanje grupa.',
        'SUMMARY'             => 'Kratki opis grupe',
        'UPDATE'              => 'Detalji izmenjeni za grupu <strong>{{name}}</strong>',
    ],

    'MANUALLY_ACTIVATED'    => '{{user_name}}-ov nalog je manualno aktiviran',
    'MASTER_ACCOUNT_EXISTS' => 'Glavni nalog već postoji!',
    'MIGRATION'             => [
        'REQUIRED'          => 'Potreban je update baze podataka',
    ],

    'PERMISSION' => [
        1 => 'Dozvola',
        2 => 'Dozvole',

        'ASSIGN_NEW'        => 'Dodeli novu dozvolu',
        'HOOK_CONDITION'    => 'Kuka/Uslov',
        'ID'                => 'ID dozvole',
        'INFO_PAGE'         => "Stranica informacija za dozvolu '{{name}}'",
        'MANAGE'            => 'Upravljaj dozvolama',
        'NOTE_READ_ONLY'    => '<strong>Napomena:</strong> dozvole se smatraju "delom koda" i ne mogu se menjati preko admin panel-a. Kako bi ste dodali, obrisali ili modifikovali dozvole morate koristiti <a href="https://learn.userfrosting.com/database/extending-the-database" target="about:_blank">migracije baze podataka.</a>',
        'PAGE_DESCRIPTION'  => 'Listing dozvola na vašem sajtu. Stranica pruža mogućnosti editovanja i brisanja dozvola.',
        'SUMMARY'           => 'Kratki opis doyvole',
        'UPDATE'            => 'Update permissions',
        'VIA_ROLES'         => 'Ime dozvole preko uloga',
    ],

    'ROLE' => [
        1 => 'Uloga',
        2 => 'Uloge',

        'ASSIGN_NEW'          => 'Dodeli novu ulogu',
        'CREATE'              => 'Napravi novu ulogu',
        'CREATION_SUCCESSFUL' => 'Uspešno ste kreirali ulogu <strong>{{name}}</strong>',
        'DELETE'              => 'Obriši ulogu',
        'DELETE_CONFIRM'      => 'Da li ste sigurni da želite da obrišete ulogu <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Nije moguće pbrisati ulogu <strong>{{name}}</strong> zbog toga što je to podrazumevana uloga za nove korisnike.',
        'DELETE_YES'          => 'Da, obriši ulogu',
        'DELETION_SUCCESSFUL' => 'Uspešno ste obrisali ulogu <strong>{{name}}</strong>',
        'EDIT'                => 'Izmeni ulogu',
        'HAS_USERS'           => 'Akcija nije moguća zbog toga što i dalje postoje korisnici sa ulogom <strong>{{name}}</strong>.',
        'INFO_PAGE'           => 'Stranica informacija za ulogu {{name}}',
        'MANAGE'              => 'Upravljaj ulogama',
        'NAME'                => 'Naziv',
        'NAME_EXPLAIN'        => 'Molimo unesite naziv uloge',
        'NAME_IN_USE'         => 'Uloga sa nazivom <strong>{{name}}</strong> već postoji',
        'PAGE_DESCRIPTION'    => 'Listing uloga na vašem sajtu. Stranica pruža mogućnosti za izmenu i brisanje uloga.',
        'PERMISSIONS_UPDATED' => 'Dozvole izmenjene za ulogu <strong>{{name}}</strong>',
        'SUMMARY'             => 'Kretki opis uloge',
        'UPDATED'             => 'Detalji izmenjeni za ulogu <strong>{{name}}</strong>',
    ],

    'SYSTEM_INFO' => [
        '@TRANSLATION'  => 'Informacije o sistemu',

        'DB_NAME'       => 'Naziv baze podataka',
        'DB_VERSION'    => 'Verzija baze podataka',
        'DIRECTORY'     => 'Direktorijum projekta',
        'PHP_VERSION'   => 'PHP verzija',
        'SERVER'        => 'Softver web servera',
        'SPRINKLES'     => 'Učitane nadogradnje',
        'UF_VERSION'    => 'UserFrosting verzija',
        'URL'           => 'Osnovni URL sajta',
    ],

    'TOGGLE_COLUMNS' => 'Uključi/isključi kolone',

    'USER' => [
        1 => 'Korisnik',
        2 => 'Korisnici',

        'ADMIN' => [
            'CHANGE_PASSWORD'    => 'Promeni korisničku lozinku',
            'SEND_PASSWORD_LINK' => 'Pošalji korisniku link koji će mu omogućiti da odabere lozinku',
            'SET_PASSWORD'       => 'Postavi korisničku lozinku kao',
        ],

        'ACTIVATE'          => 'Aktiviraj korisnika',
        'CREATE'            => 'Kreiraj korisnika',
        'CREATED'           => 'Korisnik <strong>{{user_name}}</strong> je uspešno kreiran',
        'DELETE'            => 'Obriši korsnika',
        'DELETE_CONFIRM'    => 'Da li ste sigurni da želite da obrišete korisnika <strong>{{name}}</strong>?',
        'DELETE_YES'        => 'Da, obriši korisnika',
        'DELETED'           => 'Korisnik obrisan',
        'DISABLE'           => 'Onemogući korisnika',
        'EDIT'              => 'Izmeni korisnika',
        'ENABLE'            => 'Omogući korisnika',
        'INFO_PAGE'         => 'Informacije o korisniku {{name}}',
        'LATEST'            => 'Najnoviji korisnici',
        'PAGE_DESCRIPTION'  => 'Listing korisnika sajta. Stranica pruža mogućnosti za izmenu, manualno aktiviranje, omogućivanje/onemogućivanje korisnika i više.',
        'SUMMARY'           => 'Kratki siže naloga',
        'VIEW_ALL'          => 'Vidi sve korisnike',
        'WITH_PERMISSION'   => 'Korisnici sa ovom dozvolom',
    ],
    'X_USER' => [
        0 => 'Nema korisnika',
        1 => '{{plural}} korisnik',
        2 => '{{plural}} korisnika',
    ],
];
