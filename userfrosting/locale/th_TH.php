<?php

/**
 * th_TH
 *
 * Thai message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n 
 * @author @popiazaza
 */

/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "การลงทะเบียนนั้นเร็วและง่ายดาย",
	"MENU_USERS" => "ผู้ใช้",
	"MENU_CONFIGURATION" => "การปรับแต่ง",
	"MENU_SITE_SETTINGS" => "การตั้งค่าเว็บ",
	"MENU_GROUPS" => "กลุ่ม",
	"HEADER_MESSAGE_ROOT" => "คุณได้เข้าสู่ระบบในฐานะผู้ดูแลระบบ"
]);

// Installer
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "คุณไม่สามารถลงทะเบียนบัญชีผู้ดูแลได้ระบบจนกว่าระบบติดตั้งจะทำงานเสร็จสมบูรณ์!",
	"MASTER_ACCOUNT_EXISTS" => "มีบัญชีผู้ดูแลระบบอยู่ในระบบอยู่แล้ว!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "คุณไม่สามารถลงทะเบียนบัญชีผู้ใช้ได้จนกว่าจะลงทะเบียนบัญชีผู้ดูแลระบบให้เสร็จ!",
	"CONFIG_TOKEN_MISMATCH" => "ขออภัย Configuration Token นี้ไม่ถูกต้อง"
));

// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "กรุณากรอกชื่อผู้ใช้ของคุณ",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "กรุณากรอกชื่อแสดงของคุณ",
	"ACCOUNT_SPECIFY_PASSWORD" => "กรุณากรอกรหัสผ่านของคุณ",
	"ACCOUNT_SPECIFY_EMAIL" => "กรุณากรอกอีเมลของคุณ",
	"ACCOUNT_SPECIFY_CAPTCHA" => "กรุณากรอกรหัส Captcha",
	"ACCOUNT_SPECIFY_LOCALE" => "โปรดระบุสถานที่ให้ถูกต้อง",
	"ACCOUNT_INVALID_EMAIL" => "อีเมลไม่ถูกต้อง",
	"ACCOUNT_INVALID_USERNAME" => "ชื่อผู้ใช้ไม่ถูกต้อง",
	"ACCOUNT_INVALID_USER_ID" => "ไม่พบไอดีผู้ใช้ที่ร้องขอมา",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "ชื่อผู้ใช้ หรืออีเมลไม่ถูกต้อง",
	"ACCOUNT_USER_OR_PASS_INVALID" => "ชื่อผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง",
	"ACCOUNT_ALREADY_ACTIVE" => "เปิดใช้งานบัญชีของคุณแล้ว",
	"ACCOUNT_REGISTRATION_DISABLED" => "ขออภัย เราได้ปิดระบบลงทะเบียนแล้ว",
	"ACCOUNT_REGISTRATION_BROKEN" => "เราขออภัย กระบวนการลงทะเบียนของเราเกิดปัญหาขึ้น กรุณาติดต่อเราโดยตรงสำหรับการช่วยเหลือ",
	"ACCOUNT_REGISTRATION_LOGOUT" => "เราขออภัย คุณไม่สามารถลงทะเบียนผู้ใช้ขณะอยู่ในระบบ กรุณาออกจากระบบ",
	"ACCOUNT_INACTIVE" => "บัญชีของคุณยังไม่ถูกเปิดใช้งาน กรุณาตรวจสอบอีเมล และกล่องจดหมายขยะของคุณเพื่อเปิดใช้งานบัญชี",
	"ACCOUNT_DISABLED" => "บัญชีนี้ถูกปิดการใช้งานไปแล้ว กรุณาติดต่อเราเพื่อขอข้อมูลเพิ่มเติม",
	"ACCOUNT_USER_CHAR_LIMIT" => "ชื่อผู้ใช้ของคุณต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "ชื่อแสดงของคุณต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"ACCOUNT_PASS_CHAR_LIMIT" => "รหัสผ่านของคุณต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "อีเมลต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "หัวข้อต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"ACCOUNT_PASS_MISMATCH" => "รหัสผ่านกับรหัสยืนยันของคุณต้องตรงกัน",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "ชื่อแสดงสามารถมีแค่ตัวเลข และตัวอักษรเท่านั้น",
	"ACCOUNT_USERNAME_IN_USE" => "ชื่อผู้ใช้ '{{user_name}}' ถูกใช้งานไปแล้ว",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "ชื่อแสดง '{{display_name}}' ถูกใช้งานไปแล้ว",
	"ACCOUNT_EMAIL_IN_USE" => "อีเมล '{{email}}' ถูกใช้งานไปแล้ว",
	"ACCOUNT_LINK_ALREADY_SENT" => "อีเมลการเปิดใช้งานถูกส่งไปยังอีเมลนี้แล้วเมื่อ {{resend_activation_threshold}} วินาทีที่ผ่านมา กรุณาลองใหม่ในภายหลัง",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "เราได้ส่งอีเมลการเปิดใช้งานให้คุณแล้ว กรุณาตรวจสอบอีเมลของคุณ",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "กรุณากรอกรหัสผ่านใหม่",	
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "กรุณากรอกรหัสยืนยันใหม่",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "รหัสผ่านใหม่ต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",	
	"ACCOUNT_PASSWORD_INVALID" => "รหัสผ่านปัจจุบันไม่ตรงกับรหัสที่เรามีบันทึกไว้อยู่",	
	"ACCOUNT_DETAILS_UPDATED" => "อัพเดทข้อมูลบัญชีของ '{{user_name}}' แล้ว",
	"ACCOUNT_CREATION_COMPLETE" => "สร้างบัญชีสำหรับผู้ใช้ '{{user_name}}' ขึ้นแล้ว",
	"ACCOUNT_ACTIVATION_COMPLETE" => "คุณเปิดใช้งานบัญชีของคุณเรียบร้อยแล้ว คุณสามารถเข้าสู่ระบบได้แล้ว",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "คุณลงทะเบียนเรียบร้อยแล้ว คุณสามารถเข้าสู่ระบบได้แล้ว",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "คุณลงทะเบียนเรียบร้อย คุณจะได้รับอีเมลการเปิดใช้งานในอีกไม่ช้า กรุณาเปิดใช้งานบัญชีของคุณก่อนที่จะเข้าสู่ระบบ",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "คุณไม่สามารถเปลี่ยนเป็นรหัสผ่านเดิมได้",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "กรุณายืนยันรหัสผ่านเดิมของคุณ",
	"ACCOUNT_SETTINGS_UPDATED" => "อัพเดทการตั้งค่าบัญชีแล้ว",
	"ACCOUNT_PASSWORD_UPDATED" => "เปลี่ยนแปลงรหัสผ่านแล้ว",
	"ACCOUNT_EMAIL_UPDATED" => "เปลี่ยนแปลงอีเมลแล้ว",
	"ACCOUNT_TOKEN_NOT_FOUND" => "ไม่พบ Token / บัญชีถูกเปิดใช้งานไปแล้ว",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "ชื่อผู้ใช้สามารถมีแค่ตัวเลข และตัวอักษรเท่านั้น",
	"ACCOUNT_DELETE_MASTER" => "คุณไม่สามารถลบบัญชีผู้ดูแลระบบได้!",
	"ACCOUNT_DISABLE_MASTER" => "คุณไม่สามารถปิดการใช้งานบัญชีผู้ดูแลระบบได้!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "ปิดใช้งานบัญชีของผู้ใช้ '{{user_name}}' เรียบร้อยแล้ว",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "เปิดใช้งานบัญชีของผู้ใช้ '{{user_name}}' เรียบร้อยแล้ว",
	"ACCOUNT_DELETION_SUCCESSFUL" => "ลบผู้ใช้ '{{user_name}}' เรียบร้อยแล้ว",
	"ACCOUNT_MANUALLY_ACTIVATED" => "เปิดใช้งานบัญชีของ '{{user_name}}' เรียบร้อยแล้ว",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "ชื่อแสดงของ '{{user_name}}' เปลี่ยนเป็น '{{display_name}}' แล้ว",
	"ACCOUNT_TITLE_UPDATED" => "หัวข้อของ '{{user_name}}' เปลี่ยนเป็น '{{title}}' แล้ว",
	"ACCOUNT_GROUP_ADDED" => "เพิ่มผู้ใช้ไปยังกลุ่ม '{{name}}' แล้ว",
	"ACCOUNT_GROUP_REMOVED" => "ลบผู้ใช้ออกจากกลุ่ม '{{name}}' แล้ว",
	"ACCOUNT_GROUP_NOT_MEMBER" => "ผู้ใช้นี้ไม่ได้เป็นสมาชิกของกลุ่ม '{{name}}'",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "ผู้ใช้ได้เป็นสมาชิกของกลุ่ม '{{name}}' อยู่แล้ว",
	"ACCOUNT_PRIMARY_GROUP_SET" => "ตั้งค่ากลุ่มผู้ใช้หลักเรียบร้อยแล้ว",
	"ACCOUNT_WELCOME" => "ยินดีต้อนรับคุณ {{display_name}} กลับมา"
));

// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "ต้องระบุฟิลด์ '{{self}}'",
	"VALIDATE_BOOLEAN" => "ค่าของ '{{self}}' ต้องเป็น '0' หรือ '1'",
	"VALIDATE_INTEGER" => "ค่าของ '{{self}}' ต้องอยู่ในรูปแบบตัวเลข (integer)",
	"VALIDATE_ARRAY" => "ค่าของ '{{self}}' ต้องอยู่ในรูปแบบแถว (array)"
));

// Configuration
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "คุณกำลังพยายามอัพเดทการตั้งค่าของปลั๊กอิน '{{plugin}}' แต่มันไม่มีปลั๊กอินชื่อนี้",
	"CONFIG_SETTING_INVALID" => "คุณกำลังพยายามอัพเดทการตั้งค่า '{{name}}' ของปลั๊กอิน '{{plugin}}' แต่มันไม่มีอยู่",
	"CONFIG_NAME_CHAR_LIMIT" => "ชื่อเว็บต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"CONFIG_URL_CHAR_LIMIT" => "ลิงก์เว็บต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"CONFIG_EMAIL_CHAR_LIMIT" => "อีเมลเว็บต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"CONFIG_TITLE_CHAR_LIMIT" => "ชื่อหัวข้อผู้ใช้ใหม่ต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "การเปิดใช้งานด้วยอีเมลต้องเป็น `true` หรือ `false`",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "การลงทะเบียนผู้ใช้ต้องเป็น `true` หรือ `false`",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "เกณฑ์การเปิดใช้งานต้องมีระยะเวลาระหว่าง {{min}} ถึง {{max}} ชั่วโมง",
	"CONFIG_EMAIL_INVALID" => "อีเมลที่คุณป้อนไม่ถูกต้อง",
	"CONFIG_UPDATE_SUCCESSFUL" => "การตั้งค่าเว็บของคุณถูกอัพเดท คุณอาจต้องโหลดหน้าเว็บใหม่เพื่อให้การตั้งค่าทั้งหมดแสดงผล",
	"MINIFICATION_SUCCESS" => "ลดขนาดและตัดแบ่ง CSS และ JS สำหรับทุกหน้าของกลุ่มเรียบร้อยแล้ว"
));

// Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Token ลับของคุณไม่ถูกต้อง",
	"FORGOTPASS_OLD_TOKEN" => "Token หมดอายุแล้ว",
	"FORGOTPASS_COULD_NOT_UPDATE" => "ไม่สามารถอัพเดทรหัสผ่านได้",
	"FORGOTPASS_REQUEST_CANNED" => "การลืมรหัสผ่านถูกยกเลิก",
	"FORGOTPASS_REQUEST_EXISTS" => "มีการร้องขอลืมรหัสผ่านสำหรับบัญชีนี้เป็นจำนวนมากแล้ว",
	"FORGOTPASS_REQUEST_SENT" => "เราได้ส่งรหัสผ่านใหม่ไปทางอีเมลแล้ว",
	"FORGOTPASS_REQUEST_SUCCESS" => "เราได้ส่งอีเมลขั้นตอนการเข้าถึงบัญชีของคุณแล้ว"
));

// Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "มีข้อผิดพลาดอย่างร้ายแรงในการส่งเมล กรุณาติดต่อผู้ดูแลเซิฟเวอร์ของคุณ"
));

// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED"  => "การแปลงรหัสผ่านล้มเหลว กรุณาติดต่อผู้ดูแลระบบ",
	"NO_DATA" => "ไม่มีข้อมูลใดๆถูกส่ง",
	"CAPTCHA_FAIL" => "คำถามความปลอดภัยล้มเหลว",
	"CONFIRM" => "ยืนยัน",
	"DENY" => "ปฏิเสธ",
	"SUCCESS" => "เรียบร้อย",
	"ERROR" => "ผิดพลาด",
	"SERVER_ERROR" => "อุ๊ปส์ ดูเหมือนเซิฟเวอร์ของเราจะเกิดข้อผิดพลาด ถ้าคุณเป็นผู้ดูแลระบบกรุณาตรวจสอบ PHP error log",
	"NOTHING_TO_UPDATE" => "ไม่มีอะไรจะอัพเดท",
	"SQL_ERROR" => "มีข้อผิดพลาด SQL อย่างร้ายแรง",
	"FEATURE_DISABLED" => "คุณลักษณะนี้ถูกปิดใช้งานในปัจจุบัน",
	"ACCESS_DENIED" => "หืมม ดูเหมือนคุณจะไม่มีสิทธิทำแบบนี้นะ",
	"LOGIN_REQUIRED" => "ขออภัย คุณต้องเข้าสู่ระบบเพื่อเข้าถึงข้อมูลนี้",
	"LOGIN_ALREADY_COMPLETE" => "คุณอยู่ในระบบอยู่แล้ว!"
));

// Permissions
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "ไม่พบไอดีกลุ่มที่ร้องขอมา",
	"GROUP_NAME_CHAR_LIMIT" => "ชื่อกลุ่มต้องมีความยาวระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"AUTH_HOOK_CHAR_LIMIT" => "ชื่อของ Authorization hook ต้องมีความยาวอยู่ระหว่าง {{min}} ถึง {{max}} ตัวอักษร",
	"GROUP_NAME_IN_USE" => "ชื่อกลุ่ม '{{name}}' ถูกใช้งานแล้ว",
	"GROUP_DELETION_SUCCESSFUL" => "ลบกลุ่ม '{{name}}' เรียบร้อยแล้ว",
	"GROUP_CREATION_SUCCESSFUL" => "สร้างกลุ่ม '{{name}}' เรียบร้อยแล้ว",
	"GROUP_UPDATE" => "อัพเดทขอมูลของกลุ่ม '{{name}}' เรียบร้อยแล้ว",
	"CANNOT_DELETE_GROUP" => "คุณไม่สามารถลบกลุ่ม '{{name}}' ได้",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "ไม่สามารถลบกลุ่ม '{{name}}' ได้เนื่องจากมันถูกตั้งให้เป็นกลุ่มเริ่มต้นสำหรับผู้ใช้ใหม่ กรุณาเลือกกลุ่มอื่นให้เป็นกลุ่มเริ่มต้นก่อน",
	"GROUP_AUTH_EXISTS" => "กฎของกลุ่ม '{{name}}' ถูกตั้งสำหรับ hook '{{hook}}' ไปแล้ว",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "สร้างกฎของ '{{hook}}' ขึ้นสำหรับกลุ่ม '{{name}}' เรียบร้อยแล้ว",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "ลบกฎการเข้าถึงของกลุ่ม '{{name}}' สำหรับ '{{hook}}' เรียบร้อยแล้ว",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "ลบกฎการเข้าถึงของกลุ่ม '{{name}}' สำหรับ '{{hook}}' เรียบร้อยแล้ว",
    "GROUP_DEFAULT_PRIMARY_NOT_DEFINED" => "คุณไม่สามารถสร้างผู้ใช้ใหม่ได้ เนื่องจากไม่ได้ตั้งกลุ่มเริ่มต้น กรุณาตรวจสอบการตั้งค่ากลุ่มของคุณ"
));

return $lang;
