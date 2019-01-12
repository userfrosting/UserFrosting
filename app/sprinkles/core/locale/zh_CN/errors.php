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
    'ERROR' => [
        '@TRANSLATION' => '错误',

        '400' => [
            'TITLE'       => '错误 400: 无效请求',
            'DESCRIPTION' => '这好像不是你的错.',
        ],

        '404' => [
            'TITLE'       => '错误 404: 页面丢失',
            'DESCRIPTION' => '我们无法找到你想要的东西.',
            'DETAIL'      => '我们正努力寻找网页...',
            'EXPLAIN'     => '我们无法找到你想要的网页.',
            'RETURN'      => '不管怎样, 点击 <a href="{{url}}">这里</a> 返回前一页.'
        ],

        'CONFIG' => [
            'TITLE'       => 'UserFrosting 配置问题!',
            'DESCRIPTION' => '一些 UserFrosting 配置要求没有达到.',
            'DETAIL'      => '这里有些东西不正确.',
            'RETURN'      => '请更正如下问题, 然后 <a href="{{url}}">重新加载</a>.'
        ],

        'DESCRIPTION' => '我们发现一股强力干扰.',
        'DETAIL'      => '下面是我们得到的信息:',

        'ENCOUNTERED' => '嗯...发生了一些情况.  然而我们并不知道这是什么.',

        'RETURN' => '<a href="{{url}}">点击</a>返回上一页.',

        'SERVER' => '哦, 看起来我们的服务器出错了. 如果你是管理员, 请检查PHP及UF的logs.',

        'TITLE' => '强力干扰'
    ]
];
