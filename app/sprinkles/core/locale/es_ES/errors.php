<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 *
 * Spanish message token translations for the 'core' sprinkle.
 *
 * @package userfrosting\i18n\es_ES
 * @author rafa31gz
 */

return [
    "ERROR" => [
        "@TRANSLATION" => "Error",

        "400" => [
            "TITLE" => "Error 400: solicitud incorrecta",
            "DESCRIPTION" => "Probablemente no es tu culpa.",
        ],

        "404" => [
            "TITLE" => "Error 404 - Página no encontrada",
            "DESCRIPTION" => "Parece que no podemos encontrar lo que buscas.",
            "DETAIL" => "Intentamos encontrar tu página ...",
            "EXPLAIN" => "No pudimos encontrar la página que buscabas.",
            "RETURN" => 'De cualquier manera, haga clic en <a href="{{url}}"> aquí </a> para volver a la página principal.'
        ],
        
        "CONFIG" => [
            "TITLE" => "¡Problema de configuración del Servidor!",
            "DESCRIPTION" => "Algunos requisitos de configuración de Servidor no se han cumplido.",
            "DETAIL" => "Algo no está bien aquí.",
            "RETURN" => 'Corrija los siguientes errores, luego <a href="{{url}}"> recargue </a>.'
        ],
        
        "DESCRIPTION" => "Hemos sentido una gran perturbación en la Fuerza.",
        "DETAIL" => "Esto es lo que tenemos:",

        "ENCOUNTERED" => "Uhhh ... sucedió algo. No sabemos qué.",

        "MAIL" => "Error fatal al intentar enviar correo, póngase en contacto con el administrador del servidor. Si usted es el administrador, compruebe el log de errores.",

        "RETURN" => 'Haga clic en <a href="{{url}}"> aquí </a> para volver a la página principal.',

        "SERVER" => "¡Vaya, parece que nuestro servidor pudo haber metido la pata. Si eres un administrador, comprueba los registros de errores de PHP o el log de UserFrosting.",

        "TITLE" => "Perturbación en la Fuerza",
    ]
];
