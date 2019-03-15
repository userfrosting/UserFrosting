<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Chinese message token translations for the 'account' sprinkle.
 *
 * @author @BruceGui (https://github.com/BruceGui)
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION' => '账户',

        'ACCESS_DENIED' => '噢, 你好像没有权限这么做.',

        'DISABLED' => '这个账户已被禁用. 请联系我们获取更多信息.',

        'EMAIL_UPDATED' => '账户邮箱更新成功',

        'INVALID' => '此账户不存在. 可能已被删除.  请联系我们获取更多信息.',

        'MASTER_NOT_EXISTS' => '在创建超级账户之前你不能注册',
        'MY'                => '我的账户',

        'SESSION_COMPROMISED'       => '你的会话已泄露.  你应该在所有的设备上注销, 然后再登陆确保你的数据没被修改.',
        'SESSION_COMPROMISED_TITLE' => '你的账户可能被盗用',
        'SESSION_EXPIRED'           => '会话已过期.  请重新登陆.',

        'SETTINGS' => [
            '@TRANSLATION'  => '账户设置',
            'DESCRIPTION'   => '更新你的账户, 包括邮箱、姓名和密码.',
            'UPDATED'       => '账户更新成功'
        ],

        'TOOLS' => '账户工具',

        'UNVERIFIED' => '你的账户还没有验证. 检查你的(垃圾)邮箱文件夹进行验证.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'     => '我们发送了新的验证链接 {{email}}.  请检查你的收件箱或垃圾邮件进行验证.',
            'RESEND'            => '重新发送验证邮件',
            'COMPLETE'          => '你已成功验证. 现在可以登陆了.',
            'EMAIL'             => '请输入你登陆时的邮箱, 然后将会发送验证邮件.',
            'PAGE'              => '重新发送验证邮件给你的新账户.',
            'SEND'              => '为我的账户发送验证邮件',
            'TOKEN_NOT_FOUND'   => '验证令牌不存在 / 账户已经验证',
        ]
    ],

    'EMAIL' => [
        'INVALID'               => '<strong>{{email}}</strong> 没有账户注册.',
        'IN_USE'                => '邮箱 <strong>{{email}}</strong> 已被使用.',
        'VERIFICATION_REQUIRED' => '邮箱 (需要进行验证 - 请使用一个有效的!)'
    ],

    'EMAIL_OR_USERNAME' => '用户名或邮箱地址',

    'FIRST_NAME' => '名字',

    'HEADER_MESSAGE_ROOT' => '你现在以超级用户登陆',

    'LAST_NAME' => '姓氏',

    'LOCALE' => [
        'ACCOUNT' => '设置你账户的地区和语言',
        'INVALID' => '<strong>{{locale}}</strong> 不是一个有效的地区.'
    ],

    'LOGIN' => [
        '@TRANSLATION'      => '登陆',
        'ALREADY_COMPLETE'  => '你已经登陆!',
        'SOCIAL'            => '用其他方式登陆',
        'REQUIRED'          => '对不起, 你需要登陆才能获取资源.'
    ],

    'LOGOUT' => '注销',

    'NAME' => '名字',

    'NAME_AND_EMAIL' => '名字和邮箱',

    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION'   => '用 {{site_name}} 账户登陆, 或者创建新账户.',
            'SUBTITLE'      => '免费注册, 或用已有账户登陆.',
            'TITLE'         => '让我们开始吧!',
        ]
    ],

    'PASSWORD' => [
        '@TRANSLATION' => '密码',

        'BETWEEN'   => '字符长度 {{min}}-{{max}} ',

        'CONFIRM'               => '确认密码',
        'CONFIRM_CURRENT'       => '请确认当前密码',
        'CONFIRM_NEW'           => '确认新密码',
        'CONFIRM_NEW_EXPLAIN'   => '重新输入新密码',
        'CONFIRM_NEW_HELP'      => '选择了新密码时才需要',
        'CURRENT'               => '密码正确',
        'CURRENT_EXPLAIN'       => '你必须要确认密码再进行修改',

        'FORGOTTEN' => '忘记密码',
        'FORGET'    => [
            '@TRANSLATION' => '我忘记了密码',

            'COULD_NOT_UPDATE'  => '无法更新密码.',
            'EMAIL'             => '请输入你登陆时的邮箱. 重置密码的链接将会发送给你.',
            'EMAIL_SEND'        => '发送重置密码链接',
            'INVALID'           => '这个重置密码请求无法使用, 或已过期.  请 <a href="{{url}}">重新发送请求<a>.',
            'PAGE'              => '获取重置密码的链接.',
            'REQUEST_CANNED'    => '取消重置请求.',
            'REQUEST_SENT'      => '重置密码的链接已经发送 <strong>{{email}}</strong>.'
        ],

        'RESET' => [
            '@TRANSLATION'      => '重置密码',
            'CHOOSE'            => '请输入新密码.',
            'PAGE'              => '为账户设置新密码.',
            'SEND'              => '设置密码并登陆'
        ],

        'HASH_FAILED'       => '密码验证失败. 请联系网站管理.',
        'INVALID'           => '当前密码无法与记录匹配',
        'NEW'               => '新密码',
        'NOTHING_TO_UPDATE' => '新密码不能与旧密码相同',
        'UPDATED'           => '账户密码更新成功'
    ],

    'PROFILE'       => [
        'SETTINGS'  => '简介设置',
        'UPDATED'   => '简介设置成功'
    ],

    'REGISTER'      => '注册',
    'REGISTER_ME'   => '注册',

    'REGISTRATION' => [
        'BROKEN'            => '抱歉, 账户注册过程发送错误.  请联系我们寻求帮助.',
        'COMPLETE_TYPE1'    => '你已注册成功. 现在可以登陆了.',
        'COMPLETE_TYPE2'    => '成功注册. 激活链接已经发送给 <strong>{{email}}</strong>.  激活之前无法登陆.',
        'DISABLED'          => '抱歉, 账户注册以禁用.',
        'LOGOUT'            => '抱歉, 登陆时不能注册. 请先注销.',
        'WELCOME'           => '注册简单快速.'
    ],

    'RATE_LIMIT_EXCEEDED'       => '行动速度过快.  请等 {{delay}} 秒后再尝试新的操作.',
    'REMEMBER_ME'               => '记住我!',
    'REMEMBER_ME_ON_COMPUTER'   => '在此电脑上记住我 (不推荐在公共电脑上)',

    'SIGNIN'                => '登陆',
    'SIGNIN_OR_REGISTER'    => '登陆或注册',
    'SIGNUP'                => '注销',

    'TOS'           => '条款和说明',
    'TOS_AGREEMENT' => '在 {{site_title}} 注册, 你需要接收 <a {{link_attributes | raw}}>条款和说明</a>.',
    'TOS_FOR'       => '{{title}}的条款和说明',

    'USERNAME' => [
        '@TRANSLATION' => '用户名',

        'CHOOSE'        => '取一个唯一的用户名',
        'INVALID'       => '无效的用户名',
        'IN_USE'        => '用户名 <strong>{{user_name}}</strong> 已存在.',
        'NOT_AVAILABLE' => "用户名 <strong>{{user_name}}</strong> 不可用. 重新选择用户名, 或者点击 '建议'."
    ],

    'USER_ID_INVALID'       => '请求的用户不存在.',
    'USER_OR_EMAIL_INVALID' => '用户名或邮箱无效.',
    'USER_OR_PASS_INVALID'  => '没有发现用户或密码错误.',

    'WELCOME' => '欢迎回来, {{first_name}}'
];
