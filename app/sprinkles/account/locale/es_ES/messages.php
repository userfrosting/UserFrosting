<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 *
 * Spanish message token translations for the 'account' sprinkle.
 *
 * @package userfrosting\i18n\es_ES
 * @author rafa31gz
 */

return [
    "ACCOUNT" => [
        "@TRANSLATION" => "Perfil",

        "ACCESS_DENIED" => "Hmm, parece que no tienes permiso para hacer eso.",

        "DISABLED" => "Esta cuenta se ha inhabilitado. Por favor contáctenos para más información.",

        "EMAIL_UPDATED" => "Correo electrónico de la cuenta actualizado",

        "INVALID" => "Esta cuenta no existe. Puede haber sido eliminado. Por favor contáctenos para más información.",

        "MASTER_NOT_EXISTS" => "No puede registrar una cuenta hasta que se haya creado la cuenta principal.",
        "MY" => "Mi Perfil",

        "SESSION_COMPROMISED" => [
            "@TRANSLATION"  => "Su sesión ha sido comprometida. Debe desconectarse de todos los dispositivos y, a continuación, volver a iniciar sesión y asegurarse de que sus datos no han sido manipulados.",
            "TITLE"         => "Es posible que su cuenta se haya visto comprometida.",
            "TEXT"          => "Alguien puede haber utilizado su información de acceso para acceder a esta página. Para su seguridad, todas las sesiones se cerraron. <a href=\"{{url}}\"> ingrese </a> y compruebe si su actividad es sospechosa en su cuenta. También puede cambiar su contraseña."
        ],
        "SESSION_EXPIRED" => "Su sesión ha caducado. Inicie sesión nuevamente.",

        "SETTINGS" => [
            "@TRANSLATION" => "Configuraciones de la cuenta",
            "DESCRIPTION" => "Actualice la configuración de su cuenta, incluido el correo electrónico, el nombre y la contraseña.",
            "UPDATED" => "Configuración de la cuenta actualizada"
        ],

        "TOOLS" => "Herramientas de la cuenta",

        "UNVERIFIED" => "Tu cuenta aún no se ha verificado. Revise sus correos electrónicos / carpeta de spam para obtener instrucciones sobre la activación de la cuenta.",

        "VERIFICATION" => [
            "NEW_LINK_SENT" => "Hemos enviado por correo electrónico un nuevo enlace de verificación a {{email}}. Comprueba tu bandeja de entrada y las carpetas de spam para este correo electrónico.",
            "RESEND" => "Reenviar correo electrónico de verificación",
            "COMPLETE" => "Ha verificado correctamente su cuenta. Ahora puede iniciar sesión.",
            "EMAIL" => "Ingrese la dirección de correo electrónico que utilizó para registrarse y su correo electrónico de verificación será enviado de nuevo.",
            "PAGE" => "Vuelva a enviar el correo electrónico de verificación de su nueva cuenta.",
            "SEND" => "Reenviar correo de verificación",
            "TOKEN_NOT_FOUND" => "El token de verificación no existe / La cuenta ya está verificada",
        ]
    ],

    "EMAIL" => [
        "INVALID" => "No hay cuenta para <strong> {{email}} </strong>.",
        "IN_USE" => "El correo electrónico <strong> {{email}} </strong> ya está en uso.",
        "VERIFICATION_REQUIRED" => "Correo electrónico (se requiere verificación - ¡use una dirección real!)"
    ],

    "EMAIL_OR_USERNAME" => "Nombre de usuario o dirección de correo electrónico",

    "FIRST_NAME" => "Nombre",

    "HEADER_MESSAGE_ROOT" => "USTED HA INGRESADO COMO USUARIO ROOT",

    "LAST_NAME" => "Apellidos",

    "LOCALE" => [
        "ACCOUNT" => "El idioma y la configuración regional para utilizar en su cuenta",
        "INVALID" => "<strong>{{locale}}</strong> no es un idioma válido."
    ],

    "LOGIN" => [
        "@TRANSLATION" => "Acceder",
        "ALREADY_COMPLETE" => "¡Ya se ha autentificado!",
        "SOCIAL" => "O ingrese con",
        "REQUIRED" => "Lo sentimos, debes iniciar sesión para acceder a este recurso."
    ],

    "LOGOUT" => "Cerrar sesión",

    "NAME" => "Nombre",

    "NAME_AND_EMAIL" => "Nombre y correo electrónico",

    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION" => "Inicie sesión en su cuenta de {{site_name}} o regístrese para obtener una nueva cuenta.",
            "SUBTITLE" => "Regístrese gratis o inicie sesión con una cuenta existente.",
            "TITLE" => "¡Empecemos!",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION" => "Contraseña",

        "BETWEEN" => "Entre {{min}} - {{max}} (recomendado 12)",

        "CONFIRM" => "Confirmar contraseña",
        "CONFIRM_CURRENT" => "Por favor, confirma tu contraseña actual",
        "CONFIRM_NEW" => "Confirmar nueva contraseña",
        "CONFIRM_NEW_EXPLAIN" => "Vuelve a ingresar tu nueva contraseña",
        "CONFIRM_NEW_HELP" => "Sólo se requiere si se selecciona una nueva contraseña",
        "CREATE" => [
            "@TRANSLATION"  => "Crear contraseña",
            "PAGE"          => "Elija una contraseña para su nueva cuenta.",
            "SET"           => "Establecer contraseña e iniciar sesión"
        ],
        "CURRENT" => "Contraseña actual",
        "CURRENT_EXPLAIN" => "Debe confirmar su contraseña actual para realizar cambios",

        "FORGOTTEN" => "Contraseña olvidada",
        "FORGET" => [
            "@TRANSLATION" => "Olvidé mi contraseña",

            "COULD_NOT_UPDATE" => "No se pudo actualizar la contraseña.",
            "EMAIL" => "Introduce la dirección de correo electrónico que utilizaste para registrarte. Se le enviará por correo electrónico un enlace con las instrucciones para restablecer su contraseña.",
            "EMAIL_SEND" => "Contraseña de correo electrónico Restablecer enlace",
            "INVALID" => "No se pudo encontrar esta solicitud de restablecimiento de contraseña o ha caducado. Intenta <a href=\"{{url}}\"> volver a enviar tu solicitud <a>.",
            "PAGE" => "Obtenga un enlace para restablecer su contraseña.",
            "REQUEST_CANNED" => "Se ha cancelado la solicitud de contraseña perdida.",
            "REQUEST_SENT" => "Se ha enviado un enlace de restablecimiento de contraseña a <strong> {{email}} </strong>."
        ],

        "RESET" => [
            "@TRANSLATION" => "Restablecer la contraseña",
            "CHOOSE" => "Por favor, elija una nueva contraseña para continuar.",
            "PAGE" => "Elige una nueva contraseña para tu cuenta.",
            "SEND" => "Establecer nueva contraseña e iniciar sesión"
        ],

        "HASH_FAILED" => "El hash de la contraseña ha fallado. Póngase en contacto con un administrador del sitio.",
        "INVALID" => "La contraseña actual no coincide con la que tenemos registrada",
        "NEW" => "Nueva contraseña",
        "NOTHING_TO_UPDATE" => "No se puede actualizar con la misma contraseña",
        "UPDATED" => "Contraseña de la cuenta actualizada"
    ],

    "PROFILE" => [
        "SETTINGS" => "Configuración de perfil",
        "UPDATED" => "Configuración del perfil actualizada"
    ],

    "RATE_LIMIT_EXCEEDED" => "Se ha superado el límite de velocidad para esta acción. Debe esperar otro {{delay}} segundos antes de que se le permita hacer otro intento.",

    "REGISTER" => "Registro",
    "REGISTER_ME" => "Inscríbeme",
    "REGISTRATION" => [
        "BROKEN" => "Lo sentimos, hay un problema con nuestro proceso de registro de cuenta. Póngase en contacto con nosotros directamente para obtener ayuda.",
        "COMPLETE_TYPE1" => "Se ha registrado exitosamente. Ahora puede iniciar sesión.",
        "COMPLETE_TYPE2" => "Se ha registrado exitosamente. Se ha enviado un enlace para activar tu cuenta a <strong> {{email}} </strong>. No podrá iniciar sesión hasta que complete este paso.",
        "DISABLED" => "Lo sentimos, el registro de cuenta se ha deshabilitado.",
        "LOGOUT" => "Lo siento, no puede registrarse para una cuenta mientras está conectado. Por favor, cierra la sesión primero.",
        "WELCOME" => "El registro es rápido y sencillo."
    ],
   
    "REMEMBER_ME" => "¡Recuérdame!",
    "REMEMBER_ME_ON_COMPUTER" => "Recuérdeme en este ordenador (no se recomienda para ordenadores públicos)",

    "SIGNIN" => "Iniciar sesión",
    "SIGNIN_OR_REGISTER" => "Ingresa o Registro",
    "SIGNUP" => "Regístrate",
    "SUGGEST" => "Sugerencia",
    "HAVE_ACCOUNT" => "¿Ya tienes una cuenta?",
    "SIGN_IN_HERE"=> "¿Ya tienes una cuenta? <a href=\"{{url}}\"> Acceda aquí. </a>",


    "TOS" => "Términos y Condiciones",
    "TOS_AGREEMENT" => "Al registrar una cuenta con {{site_title}}, acepta los <a {{link_attributes | raw}}> términos y condiciones </a>.",
    "TOS_FOR" => "Términos y condiciones para {{title}}",

    "USERNAME" => [
        "@TRANSLATION" => "Nombre de usuario",

        "CHOOSE" => "Elige un nombre de usuario único",
        "INVALID" => "Nombre de usuario no válido",
        "IN_USE" => "El nombre de usuario <strong> {{user_name}} </strong> ya está en uso.",
        "NOT_AVAILABLE" => "El nombre de usuario <strong> {{user_name}} </strong> no está disponible. Elija otro nombre o haga clic en \"sugerir\"."
    ],

    "USER_ID_INVALID" => "El ID de usuario solicitado no existe.",
    "USER_OR_EMAIL_INVALID" => "El nombre de usuario o la dirección de correo electrónico no son válidos.",
    "USER_OR_PASS_INVALID" => "Usuario no encontrado o la contraseña no es válida.",

    "WELCOME" => "Bienvenido de nuevo, {{first_name}}"
];
