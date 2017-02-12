<?php

/**
 * ar
 *
 * Modern Standard Arabic message token translations for the error pages
 *
 * @package UserFrosting
 * @link http://wwwuserfrostingcom/components/#i18n
 * @author Alexander Weissman and Abdullah Seba
 */

return [
    "ERROR" => [
        "@TRANSLATION" => "خطأ",

        "TITLE" => "اضطراب في الموقع",
        "DESCRIPTION" => "لقد لمست اضطراب كبير في الموقع",
        "ENCOUNTERED" => "حدث شيء لا نعرف ما هو",
        "DETAIL" => "وهنا ما عندنا من معلومات",
        "RETURN" => 'اضغط <a href="{{url}}">هنا</a> للعودة إلى الصفحة الأولى',

        "SERVER" => "يبدو خادمنا قد أخطأ إذا كنت المسير، يرجى مراجعة سجلات الخطأ PHP أو UF",

        "400" => [
            "TITLE" => "الخطأ 400:اقتراح غير جيد",
            "DESCRIPTION" => "على الارجح ليس خطأك",
        ],

        "404" => [
            "TITLE" => "الخطأ 404: الصفحة غير موجودة",
            "DESCRIPTION" => " لا يبدو للعثور على ما كنت تبحث عن",
            "DETAIL" => "حاولنا العثور على صفحتك",
            "EXPLAIN" => "لم نتمكن من العثور على الصفحة التي تبحث عنها",
            "RETURN" => 'وفي كلتا الحالتين، اضغط  <a href="{{url}}">هنا</a> للعودة إلى الصفحة الأولى'
        ],

        "CONFIG" => [
            "TITLE" => "مشكلة في تكوين UserFrosting",
            "DESCRIPTION" => "لم تتحقق بعض متطلبات التكوين UserFrosting",
            "DETAIL" => "شيء ليس صحيحا هنا",
            "RETURN" => 'يرجى تصحيح الأخطاء التالية، ثم <a href="{{url}}">إعادة تحميل</a>'
        ]
    ]
];
