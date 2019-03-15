<?php
/**
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
    'ERROR' => [
        '@TRANSLATION' => 'خطأ',

        '400' => [
            'TITLE'       => 'الخطأ 400:اقتراح غير جيد',
            'DESCRIPTION' => 'على الارجح ليس خطأك',
        ],

        '404' => [
            'TITLE'       => 'الخطأ 404: الصفحة غير موجودة',
            'DESCRIPTION' => ' لا يبدو للعثور على ما كنت تبحث عن',
            'DETAIL'      => 'حاولنا العثور على صفحتك',
            'EXPLAIN'     => 'لم نتمكن من العثور على الصفحة التي تبحث عنها',
            'RETURN'      => 'وفي كلتا الحالتين، اضغط  <a href="{{url}}">هنا</a> للعودة إلى الصفحة الأولى'
        ],

        'CONFIG' => [
            'TITLE'       => 'مشكلة في تكوين UserFrosting',
            'DESCRIPTION' => 'لم تتحقق بعض متطلبات التكوين UserFrosting',
            'DETAIL'      => 'شيء ليس صحيحا هنا',
            'RETURN'      => 'يرجى تصحيح الأخطاء التالية، ثم <a href="{{url}}">إعادة تحميل</a>'
        ],

        'DESCRIPTION' => 'لقد لمست اضطراب كبير في الموقع',
        'DETAIL'      => 'وهنا ما عندنا من معلومات',

        'ENCOUNTERED' => 'حدث شيء لا نعرف ما هو',

        'MAIL' => 'خطأ فادح في محاولة البريد الإلكتروني، اتصل بمسؤول المقع إذا كنت المشرف، يرجى التحقق من التسجل البريد الإلكتروني UF',

        'RETURN' => 'اضغط <a href="{{url}}">هنا</a> للعودة إلى الصفحة الأولى',

        'SERVER' => 'يبدو خادمنا قد أخطأ إذا كنت المسير، يرجى مراجعة سجلات الخطأ PHP أو UF',

        'TITLE' => 'اضطراب في الموقع'
    ]
];
