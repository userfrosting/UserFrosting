<?php

/**
 * tr_TR
 *
 * Turkish message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Yunus Emre Cagatay
 */

/*
{{name}} - İlgili dizin tarafından yükleme sırasında değiştirilen dinamik işaretler
*/

$lang = array();

// Site Content - Site İçeriği
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "Kayıt olmak hızlı ve basittir.",
	"MENU_USERS" => "Kullanıcılar",
	"MENU_CONFIGURATION" => "Ayarlar",
	"MENU_SITE_SETTINGS" => "Site Ayarları",
	"MENU_GROUPS" => "Gruplar",
	"HEADER_MESSAGE_ROOT" => "ROOT KULLANICI OLARAK GİRİŞ YAPTINIZ"
]);

// Installer - Kurulum
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "Kurulum başarıyla tamamlanmadan root hesaba kayıt olamazsınız!",
	"MASTER_ACCOUNT_EXISTS" => "Ana hesap zaten mevcut!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "Ana hesap oluşturulmadan herhangi bir hesaba kayıt olamazsınız!",
	"CONFIG_TOKEN_MISMATCH" => "Üzgünüz, yapılandırma işareti doğru değil."
));

// Account - Hesap
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Lütfen kullanıcı adınızı giriniz.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Lütfen sitede görünecek isminizi giriniz.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Lütfen şifrenizi giriniz.",
	"ACCOUNT_SPECIFY_EMAIL" => "Lütfen e-posta adresinizi giriniz.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Lütfen resimdeki kodu girin.",
	"ACCOUNT_SPECIFY_LOCALE" => "Lütfen geçerli bir dil seçin.",
	"ACCOUNT_INVALID_EMAIL" => "Geçersiz e-posta adresi",
	"ACCOUNT_INVALID_USERNAME" => "Geçersiz kullanıcı adı",
	"ACCOUNT_INVALID_USER_ID" => "İstenen kullanıcı id numarası geçersizdir.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Kullanıcı adı veya e-posta adresi geçersiz.",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Kullanıcı adı veya şifre geçersiz.",
	"ACCOUNT_ALREADY_ACTIVE" => "Hesabınız zaten aktif edilmiş.",
	"ACCOUNT_REGISTRATION_DISABLED" => "Üzgünüz, hesap kaydı devre dışı bırakıldı.",
    "ACCOUNT_REGISTRATION_BROKEN" => "Üzgünüz, hesap kayıt işlemi ile ilgili bir sorun var. Yardım için doğrudan bizimle iletişime geçiniz.",
	"ACCOUNT_REGISTRATION_LOGOUT" => "Üzgünüm, oturum açmışken bir hesap için kayıt olamazsınız. Lütfen ilk önce hesaptan çıkış yapın.",
	"ACCOUNT_INACTIVE" => "Hesabınız aktif değil. Aktivasyon talimatları için e-postalarınızı / gereksiz kutunuzu kontrol edin.",
	"ACCOUNT_DISABLED" => "Bu hesap devre dışı bırakıldı. Lütfen daha fazla bilgi için bizimle iletişime geçin.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Kullanıcı adınız en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır.",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "Kullanıcı adı sadece alfabetik ve sayısal karakterler içerebilir",
    "ACCOUNT_USER_NO_LEAD_WS" => "Kullanıcı adı boşluk ile başlayamaz",
    "ACCOUNT_USER_NO_TRAIL_WS" => "Kullanıcı adı boşluk ile bitemez",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Görünür isminiz en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "Şifreniz en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "E-posta en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Ünvanlar en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır.",
	"ACCOUNT_PASS_MISMATCH" => "Şifreniz ve onay şifreniz eşleşmelidir",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Görünür isim sadece alfabetik ve sayısal karakterler içerebilir",
	"ACCOUNT_USERNAME_IN_USE" => "Kullanıcı adı '{{user_name}}' zaten kullanımda",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Görünür isim '{{display_name}}' zaten kullanımda",
	"ACCOUNT_EMAIL_IN_USE" => "E-posta '{{email}}' zaten kullanımda",
	"ACCOUNT_LINK_ALREADY_SENT" => "Bir onay e-postası bu e-posta adresine son {{resend_activation_threshold}} saniyede zaten gönderildi. Lütfen daha sonra tekrar deneyiniz.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Size yeni bir aktivasyon linki gönderdik, lütfen e-postanızı kontrol edin",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Lütfen yeni şifrenizi girin",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Lütfen yeni şifrenizi doğrulayın",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "Yeni şifre en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"ACCOUNT_PASSWORD_INVALID" => "Mevcut şifre kaydımızdaki ile eşleşmiyor",
	"ACCOUNT_DETAILS_UPDATED" => "Kullanıcı '{{user_name}}' için hesap detayları güncellendi",
	"ACCOUNT_CREATION_COMPLETE" => "Yeni kullanıcı '{{user_name}}' için hesap oluşturuldu.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Hesabınızı başarıyla aktifleştirdiniz. Şimdi hesaba giriş yapabilirsiniz.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Başarıyla kayıt oldunuz. Şimdi hesaba giriş yapabilirsiniz.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Başarıyla kayıt oldunuz. Yakında bir aktivasyon e-postası alacaksınız. Giriş yapabilmek için hesabınızı aktifleştirmelisiniz.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Aynı şifreyle güncelleme yapamazsınız",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Lütfen mevcut şifrenizi onaylayın",
	"ACCOUNT_SETTINGS_UPDATED" => "Hesap ayarları güncellendi",
	"ACCOUNT_PASSWORD_UPDATED" => "Hesap şifresi güncellendi",
	"ACCOUNT_EMAIL_UPDATED" => "Hesap email güncellendi",
	"ACCOUNT_TOKEN_NOT_FOUND" => "İşaret bulunmamaktadır / Hesap zaten aktifleştirilmiş",
	"ACCOUNT_DELETE_MASTER" => "Ana hesabı silemezsiniz!",
	"ACCOUNT_DISABLE_MASTER" => "Ana hesabı devre dışı bırakamazsınız!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "'{{user_name}}' kullanıcısına ait hesap başarıyla devre dışı bırakıldı.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "'{{user_name}}' kullanıcısına ait hesap başarıyla etkinleştirildi.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "'{{user_name}}' isimli kullanıcı başarıyla silindi.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "{{user_name}} kullanıcısının hesabı manuel olarak aktifleştirildi",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "{{user_name}} kullanıcısının görünür ismi '{{display_name}}' olarak değiştirildi",
	"ACCOUNT_TITLE_UPDATED" => "{{user_name}} kullanıcısının ünvanı '{{title}}' olarak değiştirildi",
	"ACCOUNT_GROUP_ADDED" => "Kullanıcı '{{name}}' grubuna eklendi.",
	"ACCOUNT_GROUP_REMOVED" => "Kullanıcı '{{name}}' grubundan çıkarıldı.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "Kullanıcı '{{name}}' grubunun bir üyesi değildir.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "Kullanıcı zaten '{{name}}' grubunun üyesidir.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "'{{user_name}}' kullanıcısının ana grubu başarıyla ayarlandı.",
	"ACCOUNT_WELCOME" => "Tekrar hoşgeldin, {{display_name}}"
));

// Generic validation - Genel doğrulama
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "'{{self}}' alanı belirtilmelidir.",
	"VALIDATE_BOOLEAN" => "'{{self}}' değeri '0' veya '1' olmalıdır.",
	"VALIDATE_INTEGER" => "'{{self}}' değeri tamsayı olmalıdır.",
	"VALIDATE_ARRAY" => "'{{self}}' değerleri bir dizi içerisinde olmaldır.",
    "VALIDATE_NO_LEAD_WS" => "'{{self}}' değeri boşlukla veya diğer sekmelerle başlayamaz",
    "VALIDATE_NO_TRAIL_WS" => "'{{self}}' değeri boşlukla veya diğer sekmelerle bitemez"
));

// Configuration - Yapılandırma
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "'{{plugin}}' eklentisinin ayarlarını güncellemeye çalışıyorsunuz, fakat o isimde eklenti bulunmamaktadır.",
	"CONFIG_SETTING_INVALID" => "'{{plugin}}' eklentisinin '{{name}}' ayarını güncellemeye çalışıyorsunuz, fakat o ayar bulunmamaktadır.",
	"CONFIG_NAME_CHAR_LIMIT" => "Site ismi en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"CONFIG_URL_CHAR_LIMIT" => "Site url adresi en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"CONFIG_EMAIL_CHAR_LIMIT" => "Site e-posta adresi en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"CONFIG_TITLE_CHAR_LIMIT" => "Yeni kullanıcı ünvanı en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "E-posta aktivasyonu `doğru` veya `yanlış` olmalıdır",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "Kullanıcı kaydı `doğru` veya `yanlış` olmalıdır",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "Aktivasyon Eşiği en az {{min}} ve en fazla {{max}} saat olmaldır",
	"CONFIG_EMAIL_INVALID" => "Girdiğiniz e-posta adresi geçersizdir",
	"CONFIG_UPDATE_SUCCESSFUL" => "Sitenizin yapılandırması güncellendi. Tüm ayarların yürürlüğe girmesi için yeni bir sayfa yüklemeniz gerekebilir",
	"MINIFICATION_SUCCESS" => "Tüm sayfa grupları için CSS ve JS başarıyla küçültülüp birleştirildi."
));

// Forgot Password - Şifre Unutma
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Gizli işaretiniz geçerli değildir",
	"FORGOTPASS_OLD_TOKEN" => "İşaret geçerlilik süresini geçirmiştir",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Şifre güncellenemedi",
	"FORGOTPASS_REQUEST_CANNED" => "Kayıp şifre talebi iptal edildi",
	"FORGOTPASS_REQUEST_EXISTS" => "Bu hesap için zaten tamamlanmamış bir kayıp şifre talebi bulunmaktadır",
	"FORGOTPASS_REQUEST_SENT" => "'{{user_name}}' kullanıcısının dosyasındaki adrese şifre yenileme linki e-posta ile gönderildi",     
	"FORGOTPASS_REQUEST_SUCCESS" => "Hesabınıza tekrar erişim kazanmak için gerekli talimatları size e-posta ile gönderdik "   
));

// Mail - E-posta
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "E-posta denemesinde önemli hata, sunucu yöneticinize başvurun",
));

// Miscellaneous - Çeşitli
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Şifre kodlama başarısız oldu. Lütfen site yöneticisine başvurun.",
	"NO_DATA" => "Geçersiz/hatalı veri gönderimi",
	"CAPTCHA_FAIL" => "Güvenlik sorusu başarız oldu",
	"CONFIRM" => "Onayla",
	"DENY" => "Reddet",
	"SUCCESS" => "Başarı",
	"ERROR" => "Hata",
	"SERVER_ERROR" => "Oops, sunucu hata yapmışa benziyor. Eğer yöneticiyseniz, lütfen PHP hata kayıtlarını inceleyin.",
	"NOTHING_TO_UPDATE" => "Güncellenecek veri bulunamadı",
	"SQL_ERROR" => "Önemli SQL hatası",
	"FEATURE_DISABLED" => "Bu özellik şu anda devre dışı",
	"ACCESS_DENIED" => "Hmm, bu işlemi yapmaya izniniz yok gibi gözüküyor.",
	"LOGIN_REQUIRED" => "Üzgünüz, bu kaynağa ulaşmak için giriş yapmış olmalısınız.",
	"LOGIN_ALREADY_COMPLETE" => "Zaten giriş yapmış durumdasınız!"
));

// Permissions - İzinler
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "Talep edilen grup id numarsı geçersiz",
	"GROUP_NAME_CHAR_LIMIT" => "Grup isimleri en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
    "AUTH_HOOK_CHAR_LIMIT" => "Yetkilendirme kanca isimleri en az {{min}} ve en fazla {{max}} karakter uzunluğunda olmalıdır",
	"GROUP_NAME_IN_USE" => "Grup ismi '{{name}}' zaten kullanımda",
	"GROUP_DELETION_SUCCESSFUL" => "'{{name}}' grubu başarıyla silindi",
	"GROUP_CREATION_SUCCESSFUL" => "'{{name}}' grubu başarıyla oluşturuldu",
	"GROUP_UPDATE" => "'{{name}}' grup detayları başarıyla güncellendi.",
	"CANNOT_DELETE_GROUP" => "'{{name}}' grubu silinemez",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "'{{name}}' grubu silinemez çünkü it is set as the default primary group for new users. Please first select a different default primary group.",
    "GROUP_AUTH_EXISTS" => "'{{name}}' grubunun zaten '{{hook}}' kancası için tanımlı bir kuralı var.",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "'{{name}}' grubuna ait '{{hook}}' için bir kural başarıyla oluşturuldu.",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "'{{hook}}' için '{{name}}' grubuna erişim verilmesi kuralı başarıyla güncellendi.",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "'{{hook}}' için '{{name}}' grubuna erişim verilmesi kuralı başarıyla silindi.",
    "GROUP_DEFAULT_PRIMARY_NOT_DEFINED" => "Yeni bir kullanıcı oluşturamazsınız çünkü tanımlanmış bir varsayılan ana grup bulunmamaktadır. Lütfen grup ayarlarınızı kontrol edin."
));

return $lang;
