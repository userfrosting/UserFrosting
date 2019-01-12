<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * French message token translations for the 'core' sprinkle.
 *
 * @author Louis Charette
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'Les valeurs de <strong>{{label}}</strong> doivent être dans un tableau.',
        'BOOLEAN'       => "La valeur de <strong>{{label}}</strong> doit être '0' ou '1'.",
        'INTEGER'       => 'La valeur de <strong>{{label}}</strong> doit être un nombre entier.',
        'INVALID_EMAIL' => 'Addresse email invalide.',
        'LENGTH_RANGE'  => 'La valeur de {{label}} doit faire entre {{min}} et {{max}} caractères.',
        'MAX_LENGTH'    => "La valeur de {{label}} doit être d'un maximum de {{max}} caractères.",
        'MIN_LENGTH'    => "La valeur de {{label}} doit être d'un minimum de {{min}} caractères.",
        'NO_LEAD_WS'    => "La valeur de <strong>{{label}}</strong> ne peut pas commencer par des espaces, des tabulations ou d'autres caractères invisibles",
        'NO_TRAIL_WS'   => "La valeur de <strong>{{label}}</strong> ne peut pas se terminer par des espaces, des tabulations ou d'autres caractères invisibles",
        'RANGE'         => 'Le champ <strong>{{label}}</strong> doit être une valeur entre {{min}} et {{max}}.',
        'REQUIRED'      => 'Le champ <strong>{{label}}</strong> doit être rempli.',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong>{{name}}</strong> ne peut pas être utilisé pour filtrer ce Sprunje.',
            'BAD_LIST'   => '<strong>{{name}}</strong> is not a valid list for this Sprunje.',
            'BAD_SORT'   => '<strong>{{name}}</strong> ne peut pas être utilisé pour trier Sprunje.'
        ]
    ]
];
