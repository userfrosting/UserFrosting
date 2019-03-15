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
    '@PLURAL_RULE' => 0,

    'ABOUT' => '关于',

    'CAPTCHA' => [
        '@TRANSLATION' => '验证码',
        'FAIL'         => 'Y验证码输入错误.',
        'SPECIFY'      => '输入验证码',
        'VERIFY'       => '验证'
    ],

    'CSRF_MISSING' => ' CSRF 标记丢失.  请尝试重新加载页面?',

    'DB_INVALID'    => '无法连接到数据库.  如果你是管理员, 请检查错误日志文件.',
    'DESCRIPTION'   => '描述',
    'DOWNLOAD'      => [
        '@TRANSLATION' => '下载',
        'CSV'          => '下载 CSV 文件'
    ],

    'EMAIL' => [
        '@TRANSLATION' => '邮件',
        'YOUR'         => '你的邮件地址'
    ],

    'HOME'  => '首页',

    'LEGAL' => '法律政策',

    'LOCALE' => [
        '@TRANSLATION' => '本地'
    ],

    'MAIL_ERROR' => '尝试发送邮件发送致命错误, 联系网站管理员.  如果你是管理员，请检查UF邮件错误日志.',

    'NAME'       => '名字',
    'NAVIGATION' => '导航',

    'PAGINATION' => [
        'GOTO' => '跳到页',
        'SHOW' => '显示'
    ],
    'PRIVACY' => '隐私政策',

    'SLUG'           => 'Slug',
    'SLUG_CONDITION' => 'Slug/Conditions',
    'SLUG_IN_USE'    => 'A <strong>{{slug}}</strong> slug already exists',
    'STATUS'         => '状态',
    'SUGGEST'        => '建议',

    'UNKNOWN' => '未知',

    // Actions words
    'ACTIONS'                  => '动作',
    'ACTIVATE'                 => '激活',
    'ACTIVE'                   => 'Active',
    'ADD'                      => '添加',
    'CANCEL'                   => '取消',
    'CONFIRM'                  => '确认',
    'CREATE'                   => '创建',
    'DELETE'                   => '删除',
    'DELETE_CONFIRM'           => '你确定要删除这个?',
    'DELETE_CONFIRM_YES'       => '是的, 删除',
    'DELETE_CONFIRM_NAMED'     => '你确定要删除 {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => '是的, 删除 {{name}}',
    'DELETE_CANNOT_UNDONE'     => '这个动作无法撤销.',
    'DELETE_NAMED'             => '删除 {{name}}',
    'DENY'                     => '拒绝',
    'DISABLE'                  => '禁用',
    'DISABLED'                 => '禁用',
    'EDIT'                     => '编辑',
    'ENABLE'                   => '启用',
    'ENABLED'                  => '启用',
    'OVERRIDE'                 => '覆盖',
    'RESET'                    => '重置',
    'SAVE'                     => '保存',
    'SEARCH'                   => '搜寻',
    'SORT'                     => '排序',
    'SUBMIT'                   => '提交',
    'PRINT'                    => '打印',
    'REMOVE'                   => '删除',
    'UNACTIVATED'              => '未激活',
    'UPDATE'                   => '更新',
    'YES'                      => '是',
    'NO'                       => '不是',
    'OPTIONAL'                 => '可选择的',

    // Misc.
    'BUILT_WITH_UF'     => '使用 <a href="http://www.userfrosting.com">UserFrosting</a>',
    'ADMINLTE_THEME_BY' => '主题作者 <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong>保留所有权'
];
