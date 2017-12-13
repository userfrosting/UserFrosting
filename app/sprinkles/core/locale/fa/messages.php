<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 *
 * Standard Farsi/Persian message token translations for the 'core' sprinkle.
 *
 * @package userfrosting\i18n\fa
 * @author aminakbari
 */

return [
    "@PLURAL_RULE" => 1,

    "ABOUT" => "درباره",

	"CAPTCHA" => [
	    "@TRANSLATION" => "کد امنیتی",
        "FAIL" => "کد امنیتی درست نیست",
        "SPECIFY" => "کد امنیتی را وارد کنید",
        "VERIFY" => "کد امنیتی را بررسی کنید"
    ],

    "CSRF_MISSING" => "سی اس آر اف توکن یافت نشد. لطفا صفحه را از نو بارگذاری کرده و دوباره تلاش کنید.",

    "DB_INVALID" => "خطا در اتصال به پایگاه داده ها. لطفا لاگ پی اچ پی را چک کنید.",
    "DESCRIPTION"   => "توضیحات",
    "DOWNLOAD" => [
        "@TRANSLATION" => "دانلود",
        "CSV" => "دانلود سی اس وی"
    ],

    "EMAIL" => [
        "@TRANSLATION" => "ایمیل",
        "YOUR" => "آدرس ایمیل"
    ],

    "HOME"  => "خانه",

    "LEGAL" => "سیاست حقوقی",

    "LOCALE" => [
        "@TRANSLATION" => "زبان"
    ],

    "NAME"  => "نام",
    "NAVIGATION" => "جهت یابی",
    "NO_RESULTS" => "با عرض پوزش، چیزی یافت نشد.",

    "PAGINATION" => [
        "GOTO" => "پرش به صفحه",
        "SHOW" => "نمایش",

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        "OUTPUT" => "{startRow} تا {endRow} از {filteredRows} ({totalRows})"
    ],
    "PRIVACY" => "سیاست حفظ حریم خصوصی",

    "SLUG" => "اسلاگ",
    "SLUG_CONDITION" => "اسلاگ/شرایط",
    "SLUG_IN_USE" => "<strong>{{slug}}</strong> وجود دارد",
    "STATUS" => "وضعیت",
    "SUGGEST" => "پیشنهاد",

    "UNKNOWN" => "ناشناخته",

    // Actions words
    "ACTIONS" => "اقدام ها",
    "ACTIVATE" => "فعال سازی",
    "ACTIVE" => "فعال",
    "ADD" => "اضافه کردن",
    "CANCEL" => "لغو",
    "CONFIRM" => "تایید",
    "CREATE" => "اضافه کردن",
    "DELETE" => "حذف",
    "DELETE_CONFIRM" => "آیا مطمئن هستید که میخواهید این را حذف کنید؟",
    "DELETE_CONFIRM_YES" => "بله، حذف شود",
    "DELETE_CONFIRM_NAMED" => "اطمینان دارید که میخواهید {{name}} را حذف کنید؟",
    "DELETE_CONFIRM_YES_NAMED" => "بله، {{name}} حذف شود",
    "DELETE_CANNOT_UNDONE" => "این عملیات قابل بازگشت نیست.",
    "DELETE_NAMED" => "{{name}} حذف شود",
    "DENY" => "انکار",
    "DISABLE" => "غیر فعال",
    "DISABLED" => "غیر فعال",
    "EDIT" => "ویرایش",
    "ENABLE" => "فعال",
    "ENABLED" => "فعال",
    "OVERRIDE" => "تغییر",
    "RESET" => "تنظیم مجدد",
    "SAVE" => "ذخیره",
    "SEARCH" => "جست و جو",
    "SORT" => "مرتب سازی",
    "SUBMIT" => "ارسال",
    "PRINT" => "چاپ",
    "REMOVE" => "حذف",
    "UNACTIVATED" => "غیر فعال",
    "UPDATE" => "به روز رسانی",
    "YES" => "بله",
    "NO" => "خیر",
    "OPTIONAL" => "اختیاری",

    // Misc.
    "BUILT_WITH_UF" => "ساخته شده با <a href=\"http://www.userfrosting.com\">یوزرفراستینگ</a>",
    "ADMINLTE_THEME_BY" => "قالب از <strong><a href=\"http://almsaeedstudio.com\">Almsaeed Studio</a>.</strong> تمامی حقوق محفوظ است"
];
