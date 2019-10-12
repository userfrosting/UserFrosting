<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * ZA Afrikaans message token translations for the 'account' sprinkle.
 *
 * @author Dowayne Breedt
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION' => 'Rekening',

        'ACCESS_DENIED' => "Hmm, dit lyk of jy nie toestemming het om dit te doen nie.",

        'DISABLED' => 'Hierdie rekening is gedeaktiveer. Kontak ons gerus vir meer inligting.',

        'EMAIL_UPDATED' => 'Rekening e-pos opgedateer',

        'INVALID' => 'Hierdie rekening bestaan nie. Dit is moontlik uitgevee. Kontak ons gerus vir meer inligting.',

        'MASTER_NOT_EXISTS' => 'U kan nie \'n rekening registreer voordat die hoofrekening geskep is nie!',
        'MY'                => 'My Rekening',

        'SESSION_COMPROMISED' => [
            '@TRANSLATION'  => 'U sessie is in die gedrang gebring. U moet op alle toestelle afmeld, dan weer aanmeld en seker maak dat daar nie met u data gepeuter is nie.',
            'TITLE'         => 'U rekening is moontlik in gedrang gebring',
            'TEXT'          => 'Iemand het moontlik u aanmeldinligting gebruik om hierdie bladsy toe te laat. Vir u veiligheid is alle sessies afgemeld <a href="{{url}}">Teken Aan</a> en kyk of u verdagte aktiwiteit in u rekening is. U wil dalk ook u wagwoord verander.',
        ],
        'SESSION_EXPIRED'       => 'Jou sessie het verval. Meld weer aan.',

        'SETTINGS' => [
            '@TRANSLATION'  => 'Rekeninginstellings',
            'DESCRIPTION'   => 'Dateer u rekeninginstellings op, insluitend e-pos, naam en wagwoord.',
            'UPDATED'       => 'Rekeninginstellings is opgedateer',
        ],

        'TOOLS' => 'Rekeninginstrumente',

        'UNVERIFIED' => 'U rekening is nog nie geverifieer nie. Gaan u e-posadres / spam-lêergids na vir instruksies vir die aktivering van die rekening.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'     => 'Ons het \'n nuwe verifikasie-skakel per e-pos gestuur aan {{email}} toe. Kontroleer asseblief u inkassie- en spam-gidse vir hierdie e-pos.',
            'RESEND'            => 'Herstuur verifikasie e-pos',
            'COMPLETE'          => 'U het u rekening suksesvol geverifieer. U kan nou aanmeld.',
            'EMAIL'             => 'Voer die e-posadres in wat u gebruik het om aan te meld, en u verifikasie-e-pos sal weer gestuur word.',
            'PAGE'              => 'Stuur die verifikasie-e-pos vir u nuwe rekening weer.',
            'SEND'              => 'Stuur \'n e-pos na die verifiëringskakel vir my rekening',
            'TOKEN_NOT_FOUND'   => 'Verifikasietoken bestaan nie / rekening is reeds geverifieer',
        ],
    ],

    'EMAIL' => [
        'INVALID'               => 'Daar is geen rekening vir <strong>{{email}}</strong>.',
        'IN_USE'                => 'E-Pos <strong>{{email}}</strong> is alreeds in gebruik.',
        'VERIFICATION_REQUIRED' => 'E-pos (verifikasie vereis - gebruik \'n regte adres!)',
    ],

    'EMAIL_OR_USERNAME' => 'Gebruikersnaam of e-posadres',

    'FIRST_NAME' => 'Eerste naam',

    'HEADER_MESSAGE_ROOT' => 'U IS AS DIE ROOT REKENING AANGETEKEN',

    'LAST_NAME' => 'Laste naam',
    'LOCALE'    => [
        'ACCOUNT' => 'Die taal en plek om te gebruik vir u rekening',
        'INVALID' => '<strong>{{locale}}</strong> is nie \'n geldige plek nie.',
    ],
    'LOGIN' => [
        '@TRANSLATION'      => 'Teken Aan',
        'ALREADY_COMPLETE'  => 'Jy is alreeds ingeteken!',
        'SOCIAL'            => 'Of teken aan met',
        'REQUIRED'          => 'Jammer, u moet aangemeld wees om toegang tot hierdie bron te verkry.',
    ],
    'LOGOUT' => 'Teken uit',

    'NAME' => 'Naam',

    'NAME_AND_EMAIL' => 'Naam en E-pos',

    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION'   => 'Meld aan by jou {{site_name}} rekening, of registreer vir \'n nuwe rekening.',
            'SUBTITLE'      => 'Registreer gratis, of meld aan met \'n bestaande rekening.',
            'TITLE'         => "Laat ons begin!",
        ],
    ],

    'PASSWORD' => [
        '@TRANSLATION' => 'Password',

        'BETWEEN'   => 'Tussen {{min}}-{{max}} karakters',

        'CONFIRM'               => 'Bevestig Wagwoord',
        'CONFIRM_CURRENT'       => 'Bevestig asseblief u huidige wagwoord',
        'CONFIRM_NEW'           => 'Bevestig nuwe wagwoord',
        'CONFIRM_NEW_EXPLAIN'   => 'Voer u nuwe wagwoord weer in',
        'CONFIRM_NEW_HELP'      => 'Slegs nodig as u \'n nuwe wagwoord kies',
        'CREATE'                => [
            '@TRANSLATION'  => 'Skep wagwoord',
            'PAGE'          => 'Kies \'n wagwoord vir u nuwe rekening.',
            'SET'           => 'Stel wagwoord en teken aan',
        ],
        'CURRENT'               => 'huidige Sleutelwoord',
        'CURRENT_EXPLAIN'       => 'U moet u huidige wagwoord bevestig om veranderinge aan te bring',

        'FORGOTTEN' => 'Vergete Wagwoord',
        'FORGET'    => [
            '@TRANSLATION' => 'ek het my wagwoord vergeet',

            'COULD_NOT_UPDATE'  => "Kon nie wagwoord opdateer nie.",
            'EMAIL'             => 'Voer die e-posadres in wat u gebruik het om aan te meld. U sal \'n skakel met instruksies om u wagwoord terug te stel, per e-pos aan u stuur.',
            'EMAIL_SEND'        => 'E-pos wagwoord herstel skakel',
            'INVALID'           => 'Hierdie wagwoordterugstellingversoek kon nie gevind word nie, of het verval. Probeer asseblief <a href="{{url}}"> dien u versoek weer in<a>.',
            'PAGE'              => 'Kry \'n skakel om u wagwoord terug te stel.',
            'REQUEST_CANNED'    => 'Wagwoord verloor het gekanselleer.',
            'REQUEST_SENT'      => 'As die e-pos <strong>{{email}}</strong> ooreenstem met \'n rekening in ons stelsel, sal \'n skakel met wagwoordterugstelling gestuur word<strong>{{email}}</strong>.',
        ],

        'HASH_FAILED'       => 'Wagwoord het misluk. Kontak \'n webwerfadministrateur.',
        'INVALID'           => "Huidige wagwoord stem nie ooreen met die een wat ons op rekord het nie",
        'NEW'               => 'Nuwe Wagwoord',
        'NOTHING_TO_UPDATE' => 'U kan nie met dieselfde wagwoord opdateer nie',

        'RESET' => [
            '@TRANSLATION'      => 'Herstel wagwoord',
            'CHOOSE'            => 'Kies \'n nuwe wagwoord om voort te gaan.',
            'PAGE'              => 'Kies \'n nuwe wagwoord vir u rekening.',
            'SEND'              => 'Stel \'n nuwe wagwoord in en teken aan',
        ],

        'UPDATED'           => 'Rekeningwagwoord opgedateer',
    ],

    'PROFILE'       => [
        'SETTINGS'  => 'Profielinstellings',
        'UPDATED'   => 'Profielinstellings is opgedateer',
    ],

    'RATE_LIMIT_EXCEEDED'       => 'Die tarieflimiet vir hierdie aksie is oorskry. U moet weer wag {{delay}} sekondes voordat u toegelaat sal word om weer te probeer.',

    'REGISTER'      => 'registreer',
    'REGISTER_ME'   => 'Teken my aan',
    'REGISTRATION'  => [
        'BROKEN'            => "Jammer, daar is 'n probleem met ons registrasieproses. Kontak ons direk vir hulp.",
        'COMPLETE_TYPE1'    => 'U het suksesvol geregistreer. U kan nou aanmeld.',
        'COMPLETE_TYPE2'    => 'U het suksesvol geregistreer. \'N Skakel om u rekening te aktiveer, is gestuur <strong>{{email}}</strong>. U sal nie eers kan aanmeld voordat u hierdie stap voltooi het nie.',
        'DISABLED'          => "Jammer, rekeningregistrasie is gedeaktiveer.",
        'LOGOUT'            => "Jammer, u kan nie vir 'n rekening registreer terwyl u aangemeld is nie. Meld eers aan.",
        'WELCOME'           => 'Registrasie is vinnig en eenvoudig.',
    ],
    'REMEMBER_ME'               => 'Hou my ingeteken',
    'REMEMBER_ME_ON_COMPUTER'   => 'Onthou my op hierdie rekenaar (word nie aanbeveel vir openbare rekenaars nie)',

    'SIGN_IN_HERE'          => 'Het jy Reeds \'n rekening?<a href="{{url}}">Meld hier aan.</a>',
    'SIGNIN'                => 'Meld aan',
    'SIGNIN_OR_REGISTER'    => 'Meld aan of registreer',
    'SIGNUP'                => 'Sign Up',

    'TOS'           => 'Bepalings en voorwaardes',
    'TOS_AGREEMENT' => 'Deur \'n rekening by {{site_title}}, <a {{link_attributes | raw}}> aanvaar jy die bepalings en voorwaardes</a>.',
    'TOS_FOR'       => 'Bepalings en voorwaardes vir{{title}}',

    'USERNAME' => [
        '@TRANSLATION' => 'Gebruikersnaam',

        'CHOOSE'        => 'Kies \'n unieke gebruikersnaam',
        'INVALID'       => 'Ongeldige gebruikersnaam',
        'IN_USE'        => 'Gebruikernaam  <strong>{{user_name}}</strong> is reeds in gebruik.',
        'NOT_AVAILABLE' => "Gebruikernaam  <strong>{{user_name}}</strong> is nie beskikbaar nie. Kies 'n ander naam, of klik op 'suggest'.",
    ],

    'USER_ID_INVALID'       => 'Die versoekte gebruikers-ID bestaan nie.',
    'USER_OR_EMAIL_INVALID' => 'Gebruikernaam of e-posadres is ongeldig.',
    'USER_OR_PASS_INVALID'  => 'Gebruiker nie gevind nie of die wagwoord is ongeldig.',

    'WELCOME' => 'Welkom terug, {{first_name}}',
];
