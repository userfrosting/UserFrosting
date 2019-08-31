<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Modern Standard Arabic message token translations for the 'core' sprinkle.
 *
 * @author Alexander Weissman and Abdullah Seba
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'القيمات ل <strong>{{label}}</strong> يجب أن تكون في مجموعة',
        'BOOLEAN'       => 'القيم ل <strong>{{label}}</strong> يجب أن يكون إما \'٠\' أو \'١\'',
        'INTEGER'       => 'القيم ل <strong>{{label}}</strong> يجب أن يكون رقم',
        'INVALID_EMAIL' => 'عنوان البريد الإلكتروني غير صالح',
        'LENGTH_RANGE'  => '{{label}} لابد ان تكون بين {{min}} و {{max}} حورف',
        'MAX_LENGTH'    => '{{label}} يجب أن يكون طول {{max}} الحد الأقصى .',
        'MIN_LENGTH'    => '{{label}} يجب أن يكون الحد الأدنى {{min}} من الأحرف في الطول.',
        'NO_LEAD_WS'    => 'القيم ل <strong>{{label}}</strong> لا يمكن أن تبدأ المساحات، علامات، أو بيضاء أخرى',
        'NO_TRAIL_WS'   => 'القيم ل <strong>{{label}}</strong> لا يمكن أن ينتهي مع مسافات، علامات، أو بيضاء أخرى',
        'RANGE'         => 'يجب أن تكون قيمة <strong> {{label}} </strong> بين {{min}} و {{max}}.',
        'REQUIRED'      => ' تحديد قيمة ل <strong>{{label}}</strong>',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong> {{name}} </strong> ليس مرشحًا صالحًا لهذا Sprunje.',
            'BAD_LIST'   => '<strong> {{name}} </strong> ليست قائمة صالحة لهذا Sprunje.',
            'BAD_SORT'   => '<strong> {{name}} </strong> ليس حقل فرز صالحًا لهذا Sprunje.',
        ],
    ],
];
