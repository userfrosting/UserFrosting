<?php

/**
 * es_ES
 *
 * Spanish (internationalized) message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n 
 * @author @jchorques
 * @author @tyl3r
 */

/*
{{name}} - Dynamic markers which are replaced at run time by the relevant index.
*/

$lang = array();
// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "El registro es rápido y sencillo.",
	"MENU_USERS" => "Usuarios",
	"MENU_CONFIGURATION" => "Configuración",
	"MENU_SITE_SETTINGS" => "Configuración del Sitio",
	"MENU_GROUPS" => "Grupos",
	"HEADER_MESSAGE_ROOT" => "Has iniciado sesión como usuario root"
]);
// Installer
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "¡No puedes registrar la cuenta maestra hasta que el instalador haya finalizado!",
	"MASTER_ACCOUNT_EXISTS" => "¡Ya existe una cuenta maestra!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "¡No puedes registrar ninguna cuenta hasta que la cuenta maestra haya sido creada!",
	"CONFIG_TOKEN_MISMATCH" => "El token de configuración no es correcto."
));
// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Introduce tu nombre de usuario.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Introduce tu nombre público.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Introduce tu contraseña.",
	"ACCOUNT_SPECIFY_EMAIL" => "Introduce tu dirección de correo electrónico.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Introduce el código de la imagen (captcha).",
	"ACCOUNT_SPECIFY_LOCALE" => "Por favor, especifica una localización válida.",
	"ACCOUNT_INVALID_EMAIL" => "Dirección de correo electrónico no válida",
	"ACCOUNT_INVALID_USERNAME" => "Nombre de usuario no válido",
	"ACCOUNT_INVALID_USER_ID" => "No existe el identificador de usuario solicitado.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Nombre de usuario o dirección de correo electrónico no válido.",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Nombre de usuario o contraseña no válido.",
	"ACCOUNT_ALREADY_ACTIVE" => "Tu cuenta ya está activada.",
	"ACCOUNT_REGISTRATION_DISABLED" => "Lo sentimos, el registro de cuentas ha sido deshabilitado.",
	"ACCOUNT_REGISTRATION_LOGOUT" => "No puedes registrar una cuenta nueva con tu sesión iniciada, para hacerlo debes desconectarte primero.",
	"ACCOUNT_INACTIVE" => "Tu cuenta está inactiva. Comprueba el correo electrónico que te hemos enviado para ver las instrucciones de activación.",
	"ACCOUNT_DISABLED" => "Esta cuenta ha sido deshabilitada. Póngase en contacto con nosotros para obtener más información.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Tu nombre de usuario debe estar entre {{min}} y {{max}} caracteres de longitud.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Tu nombre público debe estar entre {{min}} y {{max}} caracteres de longitud.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "La contraseña debe tener entre {{min}} y {{max}} caracteres de longitud.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "El correo electrónico debe tener entre {{min}} y {{max}} caracteres de longitud.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Los títulos deben estar entre {{min}} y {{max}} caracteres de longitud.",
	"ACCOUNT_PASS_MISMATCH" => "Tu contraseña y confirmación de contraseña deben coincidir",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "El nombre público sólo puede incluir caracteres alfanuméricos",
	"ACCOUNT_USERNAME_IN_USE" => "El usuario '{{user_name}}' ya existe",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "El nombre público '{{display_name}}' ya existe",
	"ACCOUNT_EMAIL_IN_USE" => "El correo electrónico '{{email}}' ya está registrado",
	"ACCOUNT_LINK_ALREADY_SENT" => "Ya ha sido enviado un enlace de activación a esta dirección de correo electrónico en los últimos {{resend_activation_threshold}} segundos. Inténtalo de nuevo más tarde.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Te hemos enviado un nuevo enlace de activación, por favor revisa el correo electrónico",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Introduce Tu nueva contraseña",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Confirma tu nueva contraseña",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "La nueva contraseña debe tener entre {{min}} y {{max}} caracteres de longitud",
	"ACCOUNT_PASSWORD_INVALID" => "La contraseña actual no coincide con la que tenías",
	"ACCOUNT_DETAILS_UPDATED" => "Se han actualizado los detalles de la cuenta de '{{user_name}}'",
	"ACCOUNT_CREATION_COMPLETE" => "Se ha creado la cuenta de usuario '{{user_name}}'.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Has activado tu cuenta correctamente. Ahora ya puedes iniciar sesión.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Te has registrado con éxito. Ahora ya puedes iniciar sesión.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Te has registrado con éxito. En breve recibirás el enlace de activación en tu correo electrónico. Recuerda que debes activar tu cuenta para poder iniciar sesión.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "No puedes actualizar con la misma contraseña",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Confirma tu contraseña actual",
	"ACCOUNT_SETTINGS_UPDATED" => "Preferencias de la cuenta actualizadas",
	"ACCOUNT_PASSWORD_UPDATED" => "Contraseña de la cuenta actualizada",
	"ACCOUNT_EMAIL_UPDATED" => "Correo electrónico de la cuenta actualizado",
	"ACCOUNT_TOKEN_NOT_FOUND" => "El token no existe / La cuenta ya está activada",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "El nombre de usuario sólo pueden incluir caracteres alfanuméricos",
	"ACCOUNT_DELETE_MASTER" => "¡No puedes eliminar la cuenta maestra!",
	"ACCOUNT_DISABLE_MASTER" => "¡No puedes desactivar la cuenta maestra!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "La cuenta del usuario '{{user_name}}' se ha desactivado con éxito.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "La cuenta del usuario '{{user_name}}' se ha activado con éxito.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "La cuenta del usuario '{{user_name}}' se ha eliminado con éxito.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "La cuenta del usuario '{{user_name}}' ha sido activada manualmente",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "El usuario {{user_name}} ha cambiado su nombre público a '{{display_name}}'",
	"ACCOUNT_TITLE_UPDATED" => "El usuario {{user_name}} ha cambiado su título a '{{title}}'",
	"ACCOUNT_GROUP_ADDED" => "Usuario añadido al grupo '{{name}}'.",
	"ACCOUNT_GROUP_REMOVED" => "Usuario eliminado del grupo '{{name}}'.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "El usuario no es miembro del grupo '{{name}}'.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "El usuario ya es miembro del grupo '{{name}}'.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Grupo primario fijado correctamente para '{{user_name}}'.",
	"ACCOUNT_WELCOME" => "Bienvenid@ de nuevo, {{display_name}}"	
));
// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "El campo '{{self}}' es requerido.",
	"VALIDATE_BOOLEAN" => "El valor para '{{self}}' debe ser '0' o '1'.",
	"VALIDATE_INTEGER" => "El valor para '{{self}}' debe ser un número entero.",
	"VALIDATE_ARRAY" => "El valor para '{{self}}' debe estar en un array."
));
// Configuration
$lang = array_merge($lang,array(
	"CONFIG_PLUGIN_INVALID" => "Estás tratando de actualizar los ajustes del plugin '{{plugin}}', no existe ningún plugin con ese nombre.",
	"CONFIG_SETTING_INVALID" => "Estás tratando de actualizar el ajuste '{{name}}' del plugin '{{plugin}}', este ajuste no existe.",
	"CONFIG_NAME_CHAR_LIMIT" => "El nombre del sitio debe tener entre {{min}} y {{max}} caracteres de longitud",
	"CONFIG_URL_CHAR_LIMIT" => "La dirección url del sitio debe tener entre {{min}} y {{max}} caracteres de longitud",
	"CONFIG_EMAIL_CHAR_LIMIT" => "El correo electrónico del sitio debe tener entre {{min}} y {{max}} caracteres de longitud",
	"CONFIG_TITLE_CHAR_LIMIT" => "El nuevo título de usuario debe tener entre {{min}} y {{max}} caracteres de longitud",
	"CONFIG_ACTIVATION_TRUE_FALSE" => "La activación de correo electrónico debe ser `true` o `false`",
	"CONFIG_REGISTRATION_TRUE_FALSE" => "El registro de usuario debe ser `true` o `false`",
	"CONFIG_ACTIVATION_RESEND_RANGE" => "El umbral de activación debe estar entre {{min}} y {{max}} horas",
	"CONFIG_EMAIL_INVALID" => "El correo electrónico introducido no es válido",
	"CONFIG_UPDATE_SUCCESSFUL" => "La configuración del sitio se ha actualizado correctamente. Debes actualizar la página o navegar a una nueva para que los cambios tengan efecto",
	"MINIFICATION_SUCCESS" => "Los archivos CSS/JS se han reducido y concadenado para todos los grupos de páginas."
));
// Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "El token de activación no es válido",
	"FORGOTPASS_OLD_TOKEN" => "El token ha caducado",
	"FORGOTPASS_COULD_NOT_UPDATE" => "No se pudo actualizar la contraseña",
	"FORGOTPASS_NEW_PASS_EMAIL" => "Te hemos enviado una nueva contraseña por correo electrónico",
	"FORGOTPASS_REQUEST_CANNED" => "Solicitud de contraseña perdida cancelada",
	"FORGOTPASS_REQUEST_EXISTS" => "Ya existe una solicitud de contraseña perdida en esta cuenta",
	"FORGOTPASS_REQUEST_SUCCESS" => "Te hemos enviado un correo electrónico con las instrucciones para recuperar el acceso a tu cuenta"
));
// Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "Error fatal relacionado con el correo. Por favor, contacta con el administrador del servidor",
));
// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "El hashing de la contraseña ha fallado. Por favor, contacta con el administrador del sitio.",
	"NO_DATA" => "No hay datos/Datos incorrectos enviados",
	"CAPTCHA_FAIL" => "Error en la pregunta de seguridad",
	"CONFIRM" => "Confirmar",
	"DENY" => "Denegar",
	"SUCCESS" => "Éxito",
	"ERROR" => "Error",
	"SERVER_ERROR" => "Ups, parece que nuestro servidor ha metido la pata. Si eres administrador revisa el log de errores de PHP.",
	"NOTHING_TO_UPDATE" => "No hay nada que actualizar",
	"SQL_ERROR" => "Error fatal de SQL",
	"FEATURE_DISABLED" => "Esta característica se encuentra actualmente deshabilitada",
	"ACCESS_DENIED" => "Mmm, parece que no tienes permiso para hacer esto.",
	"LOGIN_REQUIRED" => "Lo siento, debes iniciar sesión para acceder a este recurso.",
	"LOGIN_ALREADY_COMPLETE" => "¡Ya has iniciado sesión!"
));
// Permissions
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "El id de grupo solicitado no existe",
	"GROUP_NAME_CHAR_LIMIT" => "Los nombres de grupo deben estar entre {{min}} y {{max}} caracteres de longitud",
	"GROUP_NAME_IN_USE" => "El nombre de grupo '{{name}}' ya está en uso",
	"GROUP_DELETION_SUCCESSFUL" => "El grupo '{{name}}' se ha eliminado correctamente",
	"GROUP_CREATION_SUCCESSFUL" => "El grupo '{{name}}' se ha creado correctamente",
	"GROUP_UPDATE" => "Los detalles del grupo '{{name}}' se han actualizado correctamente.",
	"CANNOT_DELETE_GROUP" => "El grupo '{{name}}' no puede eliminarse",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "El grupo '{{name}}' no puede eliminarse porque está asignado como grupo primario para los nuevos usuarios. Para eliminarlo primero debes establecer otro grupo primario como predefinido."
));
return $lang;
