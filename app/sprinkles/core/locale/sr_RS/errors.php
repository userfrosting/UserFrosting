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
 * @author
 */

return [
    'ERROR' => [
        '@TRANSLATION' => 'Greška',

        '400' => [
            'TITLE'       => 'Kod greške 400: Neispravan zahtev',
            'DESCRIPTION' => "Ovo verovatno nije Vaša krivica.",
        ],

        '404' => [
            'TITLE'       => 'Kod greške 404: Nije pronađeno',
            'DESCRIPTION' => "Ne možemo da pronađemo stranicu koju tražite.",
            'DETAIL'      => 'Probali smo da pronađemo stranicu koju ste tražili...',
            'EXPLAIN'     => 'Nismo mogli da pronađemo stranicu koju tražite.',
            'RETURN'      => 'U svakom slučaju, kliknite <a href="{{url}}">ovde</a> da se vratite na početnu stranicu.',
        ],

        'CONFIG' => [
            'TITLE'       => 'Problem u podešavanju UserFrosting-a!',
            'DESCRIPTION' => 'Neki od zahteva u konfiguraciji UserFrosting-a nisu ispunjeni.',
            'DETAIL'      => "Nešto nije u redu.",
            'RETURN'      => 'Molimo ispravite sledeće greške, i onda <a href="{{url}}">učitajte stranicu ponovo</a>.',
        ],

        'DESCRIPTION' => "Osetili smo veliki poremećaj u sili.",
        'DETAIL'      => "Evo šta znamo:",

        'ENCOUNTERED' => "Uhhh...nešto se desilo.  Ne znamo šta.",

        'MAIL' => 'Fatalna greška pri pokušavanju slanja mail-a, kontaktirajte administratora. Ukoliko ste Vi administrator, molimo pogledajte UserFrosting log.',

        'RETURN' => 'Kliknite <a href="{{url}}">ovde</a> da se vratite na početnu stranicu.',

        'SERVER' => "Oops, došlo je do greške na serveru. Ukoliko ste Vi administrator, molimo pogledajte PHP ili UserFrosting logs.",

        'TITLE' => 'Poremećaj u sili',
    ],
];
