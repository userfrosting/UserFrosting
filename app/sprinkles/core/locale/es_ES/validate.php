<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Spanish message token translations for the 'core' sprinkle.
 *
 * @author rafa31gz
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'Los valores de <strong> {{label}} </strong> deben estar en una matriz.',
        'BOOLEAN'       => "El valor de <strong> {{label}} </strong> debe ser '0' o '1'.",
        'INTEGER'       => 'El valor de <strong> {{label}} </strong> debe ser un entero.',
        'NUMERIC'       => 'El valor de <strong> {{label}} </strong> debe ser sólo números.',
        'INVALID_EMAIL' => 'Dirección de correo electrónico no válida.',
        'LENGTH_RANGE'  => '{{label}} debe tener entre {{min}} y {{max}} caracteres de longitud.',
        'MAX_LENGTH'    => '{{label}} debe tener un máximo de {{max}} caracteres de longitud.',
        'MIN_LENGTH'    => '{{label}} debe tener un mínimo de {{min}} caracteres de longitud.',
        'NO_LEAD_WS'    => 'El valor de <strong> {{label}} </strong> no puede comenzar con espacios, pestañas u otros espacios en blanco.',
        'NO_TRAIL_WS'   => 'El valor de <strong> {{label}} </strong> no puede finalizar con espacios, pestañas u otros espacios en blanco.',
        'RANGE'         => 'El valor de <strong> {{label}} </strong> debe estar entre {{min}} y {{max}}.',
        'REQUIRED'      => 'Especifica un valor para <strong> {{label}} </strong>.',
        'PHONE'         => 'El número proporcionado para el télefono es inválido.',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong> {{name}} </strong> no es un filtro válido para este Sprunje.',
            'BAD_LIST'   => '<strong> {{name}} </strong> no es una lista válida para este Sprunje.',
            'BAD_SORT'   => '<strong>{{name}}</strong> no es un campo de clasificación válido para este Sprunje.'
        ]
    ]
];
