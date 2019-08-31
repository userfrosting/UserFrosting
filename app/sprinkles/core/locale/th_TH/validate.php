<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Thai message token translations for the 'core' sprinkle.
 *
 * @author Karuhut Komol
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็น Array',
        'BOOLEAN'       => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็น \'0\' หรือ \'1\'',
        'INTEGER'       => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็นตัวเลข',
        'INVALID_EMAIL' => 'ที่อยู่อีเมลไม่ถูกต้อง',
        'LENGTH_RANGE'  => 'ความยาวของ {{label}} จะต้องอยู่ระหว่าง {{min}} ถึง {{max}} ตัวอักษร',
        // 'MAX_LENGTH'    => '{{label}} must be maximum {{max}} characters in length.',
        // 'MIN_LENGTH'    => '{{label}} must be minimum {{min}} characters in length.',
        'NO_LEAD_WS'    => 'ค่าของ <strong>{{label}}</strong> ไม่สามารถเริ่มต้นด้วยช่องว่าง หรือ แท็บ',
        'NO_TRAIL_WS'   => 'ค่าของ  <strong>{{label}}</strong> ไม่สามารถลงท้ายด้วยช่องว่าง หรือ แท็บ',
        // 'RANGE'         => 'The value for <strong>{{label}}</strong> must be between {{min}} and {{max}}.',
        'REQUIRED'      => 'กรุณากำหนดค่าของ <strong>{{label}}</strong>',
        'SPRUNJE'       => [
            // 'BAD_FILTER' => '<strong>{{name}}</strong> is not a valid filter for this Sprunje.',
            // 'BAD_LIST'   => '<strong>{{name}}</strong> is not a valid list for this Sprunje.',
            // 'BAD_SORT'   => '<strong>{{name}}</strong> is not a valid sort field for this Sprunje.',
        ],
    ],
];
