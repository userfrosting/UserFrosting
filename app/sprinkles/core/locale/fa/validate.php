<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Standard Farsi/Persian message token translations for the 'core' sprinkle.
 *
 * @author aminakbari
 */
return [
    'VALIDATE' => [
        'ARRAY'         => 'مقادیر <strong>{{label}}</strong> باید از یک آرایه باشند.',
        'BOOLEAN'       => 'مقدار <strong>{{label}}</strong> باید 1 یا 0 باشد.',
        'INTEGER'       => 'مقدار <strong>{{label}}</strong> باید یک عدد اینتجر باشد.',
        'INVALID_EMAIL' => 'آدرس پست الکترونیکی صحیح نیست.',
        'LENGTH_RANGE'  => '{{label}} باید بین {{min}} و {{max}} حرف باشد.',
        // 'MAX_LENGTH'    => '{{label}} must be maximum {{max}} characters in length.',
        // 'MIN_LENGTH'    => '{{label}} must be minimum {{min}} characters in length.',
        'NO_LEAD_WS'    => 'مقدار <strong>{{label}}</strong> نباید با فاصله شروع شود.',
        'NO_TRAIL_WS'   => 'مقدار <strong>{{label}}</strong> نباید با فاصله تمام شود.',
        // 'RANGE'         => 'The value for <strong>{{label}}</strong> must be between {{min}} and {{max}}.',
        'REQUIRED'      => 'لطفا برای <strong>{{label}}</strong> مقداری تعیین کنید.',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong>{{name}}</strong> فیلتر صحیحی نیست.',
            'BAD_LIST'   => '<strong>{{name}}</strong> لیست صحیحی نیست.',
            'BAD_SORT'   => '<strong>{{name}}</strong> فیلد مرتب سازی صحیحی نیست.',
        ],
    ],
];
