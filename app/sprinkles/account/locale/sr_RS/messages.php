<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Serbian message token translations for the 'account' sprinkle.
 *
 * @author zbigcheese https://github.com/zbigcheese
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION' => 'Nalog',

        'ACCESS_DENIED' => 'Hmm, izgleda da nemate dozvolu za to.',

        'DISABLED' => 'Vaš nalog je onemogućen. Molimo Vas kontaktirajte nas za dodatne informacije.',

        'EMAIL_UPDATED' => 'Vaša email adresa je sačuvana',

        'INVALID' => 'Ovaj nalog ne postoji. Molimo Vas kontaktirajte nas za dodatne informacije.',

        'MASTER_NOT_EXISTS' => 'Ne možete registrovati nalog dok ne napravite glavni administratorski nalog!',
        'MY'                => 'Moj nalog',

        'SESSION_COMPROMISED' => [
            '@TRANSLATION'  => 'Vaša sesija je kompromitovana. Molimo izlogujte se na svim uređajima i ulogujte se ponovo kako bi verifikovali da Vaši podaci nisu izmenjeni.',
            'TITLE'         => 'Vaš nalog je potencijalno kompromitovan.',
            'TEXT'          => 'Neko drugi je koristio Vaše podatke za pristup ovoj stranici. Zbog Vaše bezbednosti, izlogovali smo Vas iz svih aktivnih sesija. Molimo <a href="{{url}}">ulogujte se</a> i proverite Vaš nalog detaljno. Preporučujemo da promenite Vašu lozinku.',
        ],
        'SESSION_EXPIRED'       => 'Vaša sesija je istekla. Molimo ulogujte se ponovo.',

        'SETTINGS' => [
            '@TRANSLATION'  => 'Podešavanja naloga',
            'DESCRIPTION'   => 'Upravljajte informacijama vezanim za Vaš nalog, uključujući email, ime i lozinku.',
            'UPDATED'       => 'Podešavanja naloga su sačuvana',
        ],

        'TOOLS' => 'Alati naloga',

        'UNVERIFIED' => 'Vaš nalog još nije verifikovan. Molimo proverite Vaš email (uključujući i spam folder) za uputstva za verifikaciju.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'     => 'Poslali smo Vam verifikacioni link na {{email}}.  Molimo proverite Vaš inbox i spam folder.',
            'RESEND'            => 'Pošalji verifikacioni email ponovo',
            'COMPLETE'          => 'Uspešno ste verifikovali Vaš naloh. Sada se možete ulogovati.',
            'EMAIL'             => 'Molimo unesite email adresu sa kojom ste registrovali nalog i verifikacioni email će Vam biti ponovo poslat.',
            'PAGE'              => 'Pošaljite verifikacioni email ponovo.',
            'SEND'              => 'Pošalji mi verifikacioni link za moj nalog na email',
            'TOKEN_NOT_FOUND'   => 'Verifikacioni token ne postoji / Account is already verifiedNalog je već verifikovan',
        ],

        'EMAIL' => [
            'INVALID'               => 'Ne postoji nalog registrovan sa <strong>{{email}}</strong>.',
            'IN_USE'                => 'Email adresa <strong>{{email}}</strong> je već iskorišćena.',
            'VERIFICATION_REQUIRED' => 'Email (potrebna verifikacija - koristite pravu email adresu!)',
        ],

        'EMAIL_OR_USERNAME' => 'Korisničko ime ili email',

        'FIRST_NAME' => 'Ime',

        'HEADER_MESSAGE_ROOT' => 'ULOGOVANI STE KAO ROOT KORISNIK',

        'LAST_NAME' => 'Prezime',
        'LOCALE'    => [
            'ACCOUNT' => 'Jezik i lokalitet za Vaš nalog',
            'INVALID' => '<strong>{{locale}}</strong> nije validan lokalitet.',
        ],
        'LOGIN' => [
            '@TRANSLATION'      => 'Uloguj se',
            'ALREADY_COMPLETE'  => 'Već ste ulogovani!',
            'SOCIAL'            => 'Ili se ulogujte sa',
            'REQUIRED'          => 'Morate biti ulogovani da bi ste videli ovu stranicu.',
        ],
        'LOGOUT' => 'Izloguj se',

        'NAME' => 'Ime',

        'NAME_AND_EMAIL' => 'Ime i email',

        'PAGE' => [
            'LOGIN' => [
                'DESCRIPTION'   => 'Ulogujte se na vaš {{site_name}} nalog, ili se registrujte.',
                'SUBTITLE'      => 'Registrujte se besplatno ili se ulogujte.',
                'TITLE'         => 'Da počnemo!',
            ],
        ],

        'PASSWORD' => [
            '@TRANSLATION' => 'Lozinka',

            'BETWEEN'   => 'Između {{min}} i {{max}} karaktera',

            'CONFIRM'               => 'Potvrdi lozinku',
            'CONFIRM_CURRENT'       => 'Molimo potvrdite trenutnu lozinku',
            'CONFIRM_NEW'           => 'Potvrdite novu lozinku',
            'CONFIRM_NEW_EXPLAIN'   => 'Ponovo unesite novu lozinku',
            'CONFIRM_NEW_HELP'      => 'Potrebno samo ukoliko unosite novu lozinku',
            'CREATE'                => [
                '@TRANSLATION'  => 'Kreiraj lozinku',
                'PAGE'          => 'Odaberite lozinku za Vaš nalog.',
                'SET'           => 'Postavite lozinku i ulogujte se',
            ],
            'CURRENT'               => 'Trenutna lozinka',
            'CURRENT_EXPLAIN'       => 'Morate upisati Vašu trenutnu lozinku kako bi ste sačuvali izmene',

            'FORGOTTEN' => 'Zaboravljena lozinka',
            'FORGET'    => [
                '@TRANSLATION' => 'Zaboravio sam lozinku',

                'COULD_NOT_UPDATE'  => 'Lozinku nije bilo moguće izmeniti.',
                'EMAIL'             => 'Molimo unesite email adresu sa kojom ste kreirali nalog. Link sa instrukcijama za resetovanje lozinke će Vam biti poslat na Vaš email.',
                'EMAIL_SEND'        => 'Link za resetovanje lozinke Vam je poslat',
                'INVALID'           => 'Zahtev za resetovanje lozinke nije pronađen ili je istekao. Molimo probajte da <a href="{{url}}">podnesete zahtev ponovo<a>.',
                'PAGE'              => 'Zatraži link za resetovanje lozinke.',
                'REQUEST_CANNED'    => 'Zahtev za izgubljenu lozinku je otkazan.',
                'REQUEST_SENT'      => 'Ukoliko email <strong>{{email}}</strong> postoji u sistemu, link za resetovanje lozinke će Vam biti poslat na <strong>{{email}}</strong>.',
            ],

            'HASH_FAILED'       => 'Enkripcija lozinke nije uspela. Molimo kontaktirajte administratora.',
            'INVALID'           => 'Lozinka se ne poklapa sa sačuvanom',
            'NEW'               => 'Nova lozinka',
            'NOTHING_TO_UPDATE' => 'Nova lozinka ne može biti ista kao prethodna',

            'RESET' => [
                '@TRANSLATION'      => 'Resetuj Lozinku',
                'CHOOSE'            => 'Molimo odaberite novu lozinku kako bi ste nastavili.',
                'PAGE'              => 'Odaberite novu lozinku za Vaš nalog.',
                'SEND'              => 'Podesi novu lozinku i uloguj se',
            ],

            'UPDATED'           => 'Lozinka je uspešno izmenjena',
        ],

        'PROFILE'       => [
            'SETTINGS'  => 'Podešavanja profila',
            'UPDATED'   => 'Podešavanja profila su sačuvana',
        ],

        'RATE_LIMIT_EXCEEDED'       => 'Prekoračili ste limit za broj zahteva. Morate sačekati {{delay}} sekundi do sledećeg pokušaja.',

        'REGISTER'      => 'Registruj se',
        'REGISTER_ME'   => 'Registruj me',
        'REGISTRATION'  => [
            'BROKEN'            => 'Žao nam je ali imamo problem sa procesom registracije. Molimo kontaktirajte nas za asistenciju.',
            'COMPLETE_TYPE1'    => 'Uspešno ste se registrovali. Sada se možete ulogovati.',
            'COMPLETE_TYPE2'    => 'Uspešno ste se registrovali. Link za verifikaciju Vašeg naloga Vam je poslat na <strong>{{email}}</strong>. Morate verifikovati Vaš nalog kako bi ste mogli da se ulogujete.',
            'DISABLED'          => 'Žao nam je ali registracija novih naloga je trenutno onemogućena.',
            'LOGOUT'            => 'Žao nam je ali ne možete da registrujete novi nalog dok ste ulogovani. Molimo prvo se izlogujte.',
            'WELCOME'           => 'Registracija je brza i jednostavna.',
        ],
        'REMEMBER_ME'               => 'Zapamti me',
        'REMEMBER_ME_ON_COMPUTER'   => 'Zapamti me na ovom uređaju (ne preporučuje se za javne uređaje)',

        'SIGN_IN_HERE'          => 'Već imate nalog? <a href="{{url}}">Ulogujte se ovde.</a>',
        'SIGNIN'                => 'Uloguj se',
        'SIGNIN_OR_REGISTER'    => 'Uloguj se ili registruj nalog',
        'SIGNUP'                => 'Prijavi se',

        'TOS'           => 'Uslovi korišćenja',
        'TOS_AGREEMENT' => 'Registrovanjem na {{site_title}}, prihvatate <a {{link_attributes | raw}}>uslove korišćenja</a>.',
        'TOS_FOR'       => 'Uslovi korišćenja za {{title}}',

        'USERNAME' => [
            '@TRANSLATION' => 'Korisničko ime',

            'CHOOSE'        => 'Odaberite unikatno korisničko ime',
            'INVALID'       => 'Neispravno korisničko ime',
            'IN_USE'        => 'Korisničko ime <strong>{{user_name}}</strong> je već u upotrebi.',
            'NOT_AVAILABLE' => "Korisničko ime <strong>{{user_name}}</strong> nije dostupno. Odaberite drugo korisničko ime ili kliknite 'preporuči'.",
        ],

        'USER_ID_INVALID'       => 'ID korisnika ne postoji.',
        'USER_OR_EMAIL_INVALID' => 'Korisničko ime ili email nisu ispravni.',
        'USER_OR_PASS_INVALID'  => 'Korisnik nije pronađen ili je lozinka neispravna.',

        'WELCOME' => 'Dobrodošli, {{first_name}}',
    ],
];
