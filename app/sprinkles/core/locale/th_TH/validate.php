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
 * @author Atthaphon Urairat
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็น Array',
        'BOOLEAN'       => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็น \'0\' หรือ \'1\'',
        'INTEGER'       => 'ค่าของ <strong>{{label}}</strong> จะต้องเป็นตัวเลข',
        'INVALID_EMAIL' => 'ที่อยู่อีเมลไม่ถูกต้อง',
        'LENGTH_RANGE'  => 'ความยาวของ {{label}} จะต้องอยู่ระหว่าง {{min}} ถึง {{max}} ตัวอักษร',
        'MAX_LENGTH'    => '{{label}} ความยาวสูงสุดจะต้องไม่เกิน {{max}} ตัวอักษร',
        'MIN_LENGTH'    => '{{label}} ความยาวต่ำสุดจะต้องมีอย่างน้อย {{min}} ตัวอักษร',
        'NO_LEAD_WS'    => 'ค่าของ <strong>{{label}}</strong> ไม่สามารถเริ่มต้นด้วยช่องว่าง หรือ แท็บ',
        'NO_TRAIL_WS'   => 'ค่าของ  <strong>{{label}}</strong> ไม่สามารถลงท้ายด้วยช่องว่าง หรือ แท็บ',
        'RANGE'         => 'ค่าของ <strong>{{label}}</strong> ต้องอยู่ระหว่าง {{min}} ถึง {{max}}',
        'REQUIRED'      => 'กรุณากำหนดค่าของ <strong>{{label}}</strong>',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong>{{name}}</strong> ไม่ใช่ตัวกรองที่ถูกต้องสำหรับ Sprunje',
            'BAD_LIST'   => '<strong>{{name}}</strong> ไม่ใช่รายการที่ถูกต้องสำหรับ Sprunje',
            'BAD_SORT'   => '<strong>{{name}}</strong> ไม่ใช่ฟิลด์การเรียงลำดับที่ถูกต้องสำหรับ Sprunje',
        ],
    ],
];
