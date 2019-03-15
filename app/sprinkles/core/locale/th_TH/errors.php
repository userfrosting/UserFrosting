<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Thai message token translations for the 'core' sprinkle.
 *
 * @author Karuhut Komol
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'ข้อผิดพลาด',

        '400' => [
            'TITLE'       => 'ข้อผิดพลาด 400: การร้องขอไม่ถูกต้อง',
            'DESCRIPTION' => 'นี่ไม่น่าจะเป็นความผิดพลาดของคุณ',
        ],

        '404' => [
            'TITLE'       => 'ข้อผิดพลาด 404: ไม่พบหน้านี้',
            'DESCRIPTION' => 'ดูเหมือนเราจะไม่สามารถหาสิ่งที่คุณต้องการได้',
            'DETAIL'      => 'เราพยายามได้ที่จะหาหน้าของคุณ...',
            'EXPLAIN'     => 'เราไม่สามารถหาหน้าที่คุณมองหาอยู่ได้',
            'RETURN'      => 'อย่างไรก็ตาม คลิก <a href="{{url}}">ที่นี่</a> เพื่อกลับไปยังหน้าแรก'
        ],

        'CONFIG' => [
            'TITLE'       => 'เกิดปัญหาจากการตั้งค่า UserFrosting!',
            'DESCRIPTION' => 'การตั้งค่าบางอย่างของ UserFrosting ยังไม่ตรงตามความต้องการ',
            'DETAIL'      => 'มีบางอย่างไม่ถูกต้องอยู่',
            'RETURN'      => 'กรุณาแก้ไขข้อผิดพลาดดังกล่าว จากนั้น <a href="{{url}}">โหลดหน้านี้อีกครั้ง</a>'
        ],

        'DESCRIPTION' => 'เรารู้สึกความโกลาหลในกองทัพได้เป็นอย่างดี',
        'DETAIL'      => 'นี่คือสิ่งที่เราพบ:',

        'ENCOUNTERED' => 'อืมม...บางอย่างเกิดขึ้น แต่เราไม่รู้ว่าคืออะไร',

        'MAIL' => 'เกิดข้อผิดพลาดร้ายแรงระหว่างการพยายามส่งอีเมล กรุณาติดต่อผู้ดูแลระบบของเซิฟเวอร์นี้ หากคุณเป็นผู้ดูแล กรุณาตรวจสอบบันทึกอีเมลของ UF',

        'RETURN' => 'คลิก <a href="{{url}}">ที่นี่</a> เพื่อกลับไปยังหน้าแรก',

        'SERVER' => 'โอ้ว ดูเหมือนระบบของเราอาจจะผิดพลาดเอง หากคุณเป็นผู้ดูแล กรุณาตรวจสอบบันทึกข้อผิดพลาดของ PHP หรือ UF',

        'TITLE' => 'เกิดความโกลาหลในกองทัพ'
    ]
];
