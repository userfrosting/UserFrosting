<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Chinese message token translations for the 'core' sprinkle.
 *
 * @author @BruceGui (https://github.com/BruceGui)
 */
return [
    'VALIDATE' => [
        'ARRAY'         => ' <strong>{{label}}</strong> 的值必须在一个数组中.',
        'BOOLEAN'       => " <strong>{{label}}</strong> 的值必须是 '0' 或 '1'.",
        'INTEGER'       => ' <strong>{{label}}</strong> 必须是整数.',
        'INVALID_EMAIL' => '无效的邮箱地址.',
        'LENGTH_RANGE'  => '{{label}} 的长度必须在 {{min}} - {{max}} 之间.',
        'NO_LEAD_WS'    => '<strong>{{label}}</strong> 的值不能以空格、TAB或其他空白开始.',
        'NO_TRAIL_WS'   => ' <strong>{{label}}</strong> 的值不能以空格、TAB或其他空白结束.',
        'REQUIRED'      => '请为 <strong>{{label}}</strong> 确定一个值.',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong>{{name}}</strong> 不是一个有效的 Sprunje 过滤器.',
            'BAD_SORT'   => '<strong>{{name}}</strong> 不是一个有效的 Sprunje 排序.'
        ]
    ]
];
