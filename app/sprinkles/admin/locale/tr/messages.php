<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Turkish message token translations for the 'admin' sprinkle.
 *
 * @author Dumblledore
 */
return [
    'ACTIVITY' => [
        1 => 'Etkinlik',
        2 => 'Etkinlikler',

        'LAST' => 'Son Etkinlik',
        'PAGE' => 'Kullanıcı etkinliklerinin listesi',
        'TIME' => 'Aktivite zamanı',
    ],

    'CACHE' => [
        'CLEAR'             => 'Önbelleği temizle',
        'CLEAR_CONFIRM'     => 'Site önbelleğini temizlemek istediğine emin misin?',
        'CLEAR_CONFIRM_YES' => 'Evet, önbelleği temizle',
        'CLEARED'           => 'Önbellek temizlenmesi başarıyla tamamlandı!',
    ],

    'DASHBOARD'             => 'Pano',
    'NO_FEATURES_YET'       => 'Bu hesap için herhangi bir özellik ayarlanmış gibi görünmüyor... Henüz. Belki de henüz uygulanmadı, veya belki birisi size erişim vermeyi unutttu. Her iki durumda da sizi aramızda gördüğümüze sevindik!',
    'DELETE_MASTER'         => 'Ana hesabı silemezsiniz!',
    'DELETION_SUCCESSFUL'   => 'Kullanıcı<strong>{{user_name}}</strong> silme işlemi başarıyla tamamlandı.',
    'DETAILS_UPDATED'       => 'Kullanıcı<strong>{{user_name}}</strong> için güncel hesap detayları',
    'DISABLE_MASTER'        => 'Ana hesabı devre dışı bırakamazsınız!',
    'DISABLE_SELF'          => 'Kendi hesabınızın etkinliğini sonlandıramazsınız!',
    'DISABLE_SUCCESSFUL'    => 'Kullanıcı hesabın <strong>{{user_name}}</strong>başarıyla devre dışı bırakıldı.',

    'ENABLE_SUCCESSFUL' => 'Kullanıcı hesabın<strong>{{user_name}}</strong>başarıyla etkinleştirildi.',

    'GROUP' => [
        1 => 'Grup',
        2 => 'Grıplar',

        'CREATE'              => 'Grup oluşturmak',
        'CREATION_SUCCESSFUL' => 'Grup oluşturma başarılı<strong>{{name}}</strong>',
        'DELETE'              => 'Grubu sil',
        'DELETE_CONFIRM'      => 'Grubu silmek istediğine emin misin<strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Grubu silemezsin<strong>{{name}}</strong> çünkü o yeni kayıtlanan kullanıcılar için varsayılan grup.',
        'DELETE_YES'          => 'Evet, grubu sil',
        'DELETION_SUCCESSFUL' => 'Grup silme başarılı<strong>{{name}}</strong>',
        'EDIT'                => 'Grubu düzenle',
        'ICON'                => 'Grup ikonu',
        'ICON_EXPLAIN'        => 'Grup iyileri için ikon',
        'INFO_PAGE'           => 'Grup bilgisi sayfası {{name}} için',
        'MANAGE'              => 'Grubu yönet',
        'NAME'                => 'Grup adı',
        'NAME_EXPLAIN'        => 'Lütfen grup için bir isim giriniz',
        'NOT_EMPTY'           => 'Bunu yapamazsınız çünkü hala grupla ilişkili kullanıcılar var<strong>{{name}}</strong>.',
        'PAGE_DESCRIPTION'    => 'Siten için grupların bir listesi.  Grupları silmek ve düzenlemek için yönetim araçları sağlar.',
        'SUMMARY'             => 'Grup özeti',
        'UPDATE'              => 'Grup için detaylar güncellendi<strong>{{name}}</strong>',
    ],

    'MANUALLY_ACTIVATED'    => "{{user_name}}'ın hesabı el ile aktifleştirildi",
    'MASTER_ACCOUNT_EXISTS' => 'Ana hesap zaten mevcut!',
    'MIGRATION'             => [
        'REQUIRED'          => 'Veritabanını güncellemek gerek',
    ],

    'PERMISSION' => [
        1 => 'İzin',
        2 => 'İzinler',

        'ASSIGN_NEW'        => 'Yeni izin ata',
        'HOOK_CONDITION'    => 'Kanca/Koşullar',
        'ID'                => 'İzin Kimliği',
        'INFO_PAGE'         => '{{name}} için izin bilgi sayfası',
        'MANAGE'            => 'İzinleri yönet',
        'NOTE_READ_ONLY'    => "<strong>Lütfen Dikkat</strong> izinler ''bir kodun parçası'' olarak kabul edilir ve  arayüz aracılığıyla değiştirilemez. İzln eklemek, kaldırmak ya da değiştirmek için site bakımcıları bir <a href=\"https://learn.userfrosting.com/database/extending-the-database\" target=\"about:_blank\">veritabanı geçişi</a> kullanmalıdır",
        'PAGE_DESCRIPTION'  => 'Siteniz için izinlerin bir listesi.  Düzenleme yapmak ve izinleri kaldırmak yönetim araçları temin eder.',
        'SUMMARY'           => 'İzin Özeti',
        'UPDATE'            => 'İzinlerin Güncellenmesi',
        'VIA_ROLES'         => 'Roller ile izin alımı',
    ],

    'ROLE' => [
        1 => 'Rol',
        2 => 'Roller',

        'ASSIGN_NEW'          => 'Yeni rol ata',
        'CREATE'              => 'Rol oluştur',
        'CREATION_SUCCESSFUL' => 'Rol oluşturma başarılı <strong>{{name}}</strong>',
        'DELETE'              => 'Rolü sil',
        'DELETE_CONFIRM'      => 'Rolü silmek istediğine emin misin <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Rolü silemezsin <strong>{{name}}</strong> çünkü o kaydolmuş kullanıcılar için varsayılan bir rol.',
        'DELETE_YES'          => 'Evet, rolü sil',
        'DELETION_SUCCESSFUL' => 'Rol başarıyla silindi<strong>{{name}}</strong>',
        'EDIT'                => 'Rolü düzenle',
        'HAS_USERS'           => 'Bunu yapamazsın çünkü hala bu rol ile bağlantılı kullanıcılar var<strong>{{name}}</strong>.',
        'INFO_PAGE'           => '{{name}} için rol bilgi sayfası',
        'MANAGE'              => 'Rolleri yönet',
        'NAME'                => 'Ad',
        'NAME_EXPLAIN'        => 'Lütfen rol için bir ad giriniz',
        'NAME_IN_USE'         => '<strong>{{name}}</strong> adında bir rol zaten mevcut',
        'PAGE_DESCRIPTION'    => 'Siteniz için rollerin bir listesi. Düzenlemek ve rolleri silmek için yönetim araçları sağlar.',
        'PERMISSIONS_UPDATED' => 'Rol için izinler güncellendi<strong>{{name}}</strong>',
        'SUMMARY'             => 'Rol özeti',
        'UPDATED'             => 'Rol için detaylar güncellendi<strong>{{name}}</strong>',
    ],

    'SYSTEM_INFO' => [
        '@TRANSLATION'  => 'Sistem bilgisi',

        'DB_NAME'       => 'Veritabanı adı',
        'DB_VERSION'    => 'Veritabanı sürümü',
        'DIRECTORY'     => 'Proje dizini',
        'PHP_VERSION'   => 'PHP sürümü',
        'SERVER'        => 'Web sunucu yazılımı',
        'SPRINKLES'     => 'Yüklü serpintiler',
        'UF_VERSION'    => 'UserFrosting sürümü',
        'URL'           => 'Site kök url',
    ],

    'TOGGLE_COLUMNS' => 'Sütünları değiştirme',

    'USER' => [
        1 => 'Kullanıcı',
        2 => 'Kullanıcılar',

        'ADMIN' => [
            'CHANGE_PASSWORD'    => 'Kullanıcı şifresini değiştir',
            'SEND_PASSWORD_LINK' => 'Kullanıcıya kendi şifresini seçebileceği bir bağlantı gönder',
            'SET_PASSWORD'       => 'Kullanıcının şifresi olarak ayarla',
        ],

        'ACTIVATE'          => 'Aktif Kullanıcı',
        'CREATE'            => 'Kullanıcı oluştur',
        'CREATED'           => 'Kullanıcı <strong>{{user_name}}</strong> başarıyla oluşturuldu',
        'DELETE'            => 'Kullanıcıyı sil',
        'DELETE_CONFIRM'    => 'Kullanıcıyı silmek istediğinden emin misin?<strong>{{name}}</strong>?',
        'DELETE_YES'        => 'Evet, kullanıcıyı sil',
        'DELETED'           => 'Kullanıcı silindi',
        'DISABLE'           => 'Kullanıcı devre dışı',
        'EDIT'              => 'Kullanıcıyı düzenle',
        'ENABLE'            => 'Kullanıcı etkin',
        'INFO_PAGE'         => '{{name}} için kullanıcı bilgisi',
        'LATEST'            => 'Son Kullanıcılar',
        'PAGE_DESCRIPTION'  => 'Siten için kullanıcıların listesi. Kullanıcı detaylarını düzenlemek, elle kullanıcıları aktifleştirmek, kullanıcıları etkinleştirme/devre dışı bırakma, ve daha fazlası için yönetimsel araçlar sağlar.',
        'SUMMARY'           => 'Hesap özeti',
        'VIEW_ALL'          => 'Tüm kullanıcıları göster',
        'WITH_PERMISSION'   => 'Bu izni olan kullanıcılar',
    ],
    'X_USER' => [
        0 => 'Kullanıcı yok',
        1 => '{{plural}} kullanıcı',
        2 => '{{plural}} kullanıcılar',
    ],
];
