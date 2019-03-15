<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Turkish message token translations for the 'account' sprinkle.
 *
 * @author Dumblledore
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION' => 'Hesap',

        'ACCESS_DENIED' => 'Hmm. görünüşe göre böyle bir şey için izne sahip değilsiniz.',

        'DISABLED' => 'Bu hesap durduruldu. Daha çok bilgi için bizimle iletişime geçin.',

        'EMAIL_UPDATED' => 'Hesap maili güncellendi',

        'INVALID' => 'Bu hesap bulunamadı. Silinmiş olabilir. Daha çok bilgi için bizimle iletişime geçin.',

        'MASTER_NOT_EXISTS' => 'Ana hesap oluşturuluncaya kadar bir hesap oluşturamazsın!',
        'MY'                => 'Hesabım',

        'SESSION_COMPROMISED' => [
            '@TRANSLATION'  => 'Oturumunuz tehlikeye atıldı. Tüm cihazlardan çıkmanız, daha sonra giriş yapmanız ve bilgilerinizin değiştirilmediğini kontrol etmeniz gerekir.',
            'TITLE'         => 'Hesabınız tehlikeye atılmış olabilir',
            'TEXT'          => 'Birisi bu sayfayı ele geçirmek için giriş verilerinizi kullanmış olabilir. Güvenliğiniz için tüm oturumlar günlüğe kaydedildi. Lütfen  <a href="{{url}}">giriş yapın</a>ve şüpheli hareketler için hesabınızı kontrol edin. Ayrıca şifrenizi değiştirmek isteyebilirsiniz.'
        ],
        'SESSION_EXPIRED'       => 'Oturumunuz sona erdi. Lütfen tekrar oturum açın.',

        'SETTINGS' => [
            '@TRANSLATION'  => 'Hesap ayarları',
            'DESCRIPTION'   => 'E-posta, isim ve parolanız da dahil olmak üzere hesap ayarlarınızı güncelleyin.',
            'UPDATED'       => 'Hesap ayarları güncellendi'
        ],

        'TOOLS' => 'Hesap araçları',

        'UNVERIFIED' => 'Hesap henüz onaylanmadı. Hesap etkinleştirme talimatları için e-postalarınızı ve spam klasörünüzü kontrol edin.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'     => '{{email}} için yeni bir doğrulama bağlantısı e-posta ile gönderildi. Lütfen bu e-postanın gelen kutusunu ve spam klasörlerini kontrol edin.',
            'RESEND'            => 'Doğrulama e-postasını tekrar gönder',
            'COMPLETE'          => 'Hesabınızı başarıyla doğruladınız. Şimdi giriş yapabilirsiniz.',
            'EMAIL'             => 'Kaydolmak için kullandığınız e-posta adresinizi giriniz, ve doğrulama e-postanızı tekrar gönderin.',
            'PAGE'              => 'Yeni hesabınız için doğrulama e-postasını tekrar gönder.',
            'SEND'              => 'Hesabım için doğrulama bağlantısını e-posta ile gönder',
            'TOKEN_NOT_FOUND'   => 'Doğrulama belirteci bulunumadı / Hesap zaten doğrulandı',
        ]
    ],

    'EMAIL' => [
        'INVALID'               => '<strong>{{email}}</strong> için hesap yoktur.',
        'IN_USE'                => 'E-posta <strong>{{email}}</strong> zaten kullanılıyor.',
        'VERIFICATION_REQUIRED' => 'E-posta (doğrulama gerekli - gerçek bir adres kullanın!)'
    ],

    'EMAIL_OR_USERNAME' => 'Kullanıcı adı veya e-posta adresi',

    'FIRST_NAME' => 'Adınız',

    'HEADER_MESSAGE_ROOT' => 'Kök kullanıcı olarak giriş yaptın',

    'LAST_NAME' => 'Soyadı',
    'LOCALE'    => [
        'ACCOUNT' => 'Hesabınız için kullanılacak dil ve yerel ayar',
        'INVALID' => '<strong>{{locale}}</strong> geçersiz bir yerel.'
    ],
    'LOGIN' => [
        '@TRANSLATION'      => 'Oturum Aç',
        'ALREADY_COMPLETE'  => 'Zaten oturum açtınız!',
        'SOCIAL'            => 'Veya şununla oturum aç',
        'REQUIRED'          => 'Üzgünüm, bu sayfaya ulaşmak için oturum açmalısın.'
    ],
    'LOGOUT' => 'Oturumu kapat',

    'NAME' => 'Ad',

    'NAME_AND_EMAIL' => 'Ad ve e-posta',

    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION'   => '{{site_name}} hesabınız ile giriş yapın ya da yeni bir hesap oluşturun.',
            'SUBTITLE'      => 'Ücretsiz üye ol veya mevcut bir hesap ile giriş yapın.',
            'TITLE'         => 'Hadi başlayalım!',
        ]
    ],

    'PASSWORD' => [
        '@TRANSLATION' => 'Parola',

        'BETWEEN'   => '{{min}}-{{max}} karakterler arasında',

        'CONFIRM'               => 'Şifreyi onayla',
        'CONFIRM_CURRENT'       => 'Lütfen şuanki parolanızı giriniz',
        'CONFIRM_NEW'           => 'Yeni parolayı onayla',
        'CONFIRM_NEW_EXPLAIN'   => 'Yeni parolayı tekrar gir',
        'CONFIRM_NEW_HELP'      => 'Sadece yeni bir şifre seçerseniz gerekli',
        'CREATE'                => [
            '@TRANSLATION'  => 'Parola Oluştur',
            'PAGE'          => 'Yeni hesabınız için bir şifre belirleyin.',
            'SET'           => 'Parolayı Ayarla ve Giriş Yap'
        ],
        'CURRENT'               => 'Şimdiki Parola',
        'CURRENT_EXPLAIN'       => 'Değişiklikler için şimdiki parolanız ile onaylamalısınız',

        'FORGOTTEN' => 'Unutulan Şifre',
        'FORGET'    => [
            '@TRANSLATION' => 'Şifremi unuttum',

            'COULD_NOT_UPDATE'  => 'Şifre güncellenemedi.',
            'EMAIL'             => 'Lütfen kaydolmak için kullandığınız e-posta adresini giriniz. Şifrenizi sıfırlama talimatlarıyla bir bir bağlantı e-postanıza gönderilecektir.',
            'EMAIL_SEND'        => 'E-posta şifre sıfırlama bağlantısı',
            'INVALID'           => 'Bu şifre sıfırlama isteği bulunamadı ya da süresi bitmiş. Lütfen <a href="{{url}}">isteğinizi yeniden göndermeyi<a>deneyin.',
            'PAGE'              => 'Şifrenizi sıfırlamak için bir bağlantı oluşturun.',
            'REQUEST_CANNED'    => 'Kayıp parola isteği iptal edildi.',
            'REQUEST_SENT'      => 'Eğer e-posta<strong>{{email}}</strong> sistemdeki bir hesap ile eşleşirse, bir şifre yenileme bağlantısı<strong>{{email}}</strong> gönderilir.'
        ],

        'HASH_FAILED'       => 'Parola karma başarısız oldu. Lütfen bir site yöneticisiyle iletişime geçin.',
        'INVALID'           => 'Şimdiki şifre kayıt edilen şifre ile eşleşmiyor',
        'NEW'               => 'Yeni Şifre',
        'NOTHING_TO_UPDATE' => 'Aynı şifre ile güncelleyemezsiniz',

        'RESET' => [
            '@TRANSLATION'      => 'Şifre sıfırlama',
            'CHOOSE'            => 'Lütfen devam etmek için yeni bir şifre belirleyiniz.',
            'PAGE'              => 'Hesabınız için yeni bir şifre belirleyiniz.',
            'SEND'              => 'Yeni şifre ayarla ve giriş yap'
        ],

        'UPDATED'           => 'Hesap şifresi güncellendi'
    ],

    'PROFILE'       => [
        'SETTINGS'  => 'Profil ayarları',
        'UPDATED'   => 'Profil ayarları güncellendi'
    ],

    'RATE_LIMIT_EXCEEDED'       => 'Bu işlem için belirlenen son oran aşıldı. Başka bir deneme yapmanıza izin verilene kadar {{delay}} bir süre beklemelisiniz.',

    'REGISTER'      => 'Kaydol',
    'REGISTER_ME'   => 'Beni kaydet',
    'REGISTRATION'  => [
        'BROKEN'            => 'Üzgünüz, hesap kayıt işlemimizde bir sorun var. Lütfen destek almak için doğrudan bizimle iletişime geçin.',
        'COMPLETE_TYPE1'    => 'Kaydınız başarıyla tamamlandı. Şimdi giriş yapabilirsiniz.',
        'COMPLETE_TYPE2'    => 'Kaydınız başarıyla tamamlandı. Hesabınızı aktifleştirmek için bir bağlantı gönderildi<strong>{{email}}</strong>. Bu adımı tamamlayana kadar oturum açamazsınız.',
        'DISABLED'          => 'Üzgünüz, hesap kaydı devre dışı bırakıldı.',
        'LOGOUT'            => 'Üzgünüm, oturumunuz açıkken yeni bir hesap oluşturamazsınız. Lütfen önce oturumunuzdan çıkış yapınız.',
        'WELCOME'           => 'Kaydolmak hızlı ve basittir.'
    ],
    'REMEMBER_ME'               => 'Beni hatırla!',
    'REMEMBER_ME_ON_COMPUTER'   => 'Bu bilgisayarda beni hatırla ( genel bilgisayarlar için önerilmez)',

    'SIGN_IN_HERE'          => 'Zaten bir hesaba sahip misiniz?<a href="{{url}}">burada giriş yap</a>',
    'SIGNIN'                => 'Giriş yap',
    'SIGNIN_OR_REGISTER'    => 'Giriş yap veya kayıt ol',
    'SIGNUP'                => 'Üye ol',

    'TOS'           => 'Şartlar ve Koşullar',
    'TOS_AGREEMENT' => 'Bir hesap ile kaydolarak {{site_title}} sen kabul edersin <a {{link_attributes | raw}}>şartlar ve koşulları</a>.',
    'TOS_FOR'       => '{{title}} için şartlar ve koşullar',

    'USERNAME' => [
        '@TRANSLATION' => 'Kullanıcı Adı',

        'CHOOSE'        => 'Benzersiz bir kullanıcı adı seç',
        'INVALID'       => 'Geçersiz kullanıcı adı',
        'IN_USE'        => '<strong>{{user_name}}</strong> kullanıcı adı zaten mevcut.',
        'NOT_AVAILABLE' => "<strong>{{user_name}}</strong> kullanıcı adı kullanılamaz. Farklı bir isim veya 'öneriye' tıklayın."
    ],

    'USER_ID_INVALID'       => 'İstenen kullanıcı adı mevcut değil.',
    'USER_OR_EMAIL_INVALID' => 'Kullanıcı adı veya e-posta adresi hatalı.',
    'USER_OR_PASS_INVALID'  => 'Kullanıcı bulunamadı ya da şifre hatalı.',

    'WELCOME' => 'Tekrar Hoşgeldiniz.{{first_name}}'
];
