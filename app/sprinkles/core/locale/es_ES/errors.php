<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Spanish message token translations for the 'core' sprinkle.
 *
 * @author rafa31gz
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'Error',

        '400' => [
            'TITLE'       => 'Error 400: solicitud incorrecta',
            'DESCRIPTION' => 'Probablemente no es tu culpa.',
        ],

        '404' => [
            'TITLE'       => 'Error 404 - Página no encontrada',
            'DESCRIPTION' => 'Parece que no podemos encontrar lo que buscas.',
            'DETAIL'      => 'Intentamos encontrar tu página ...',
            'EXPLAIN'     => 'No pudimos encontrar la página que buscabas.',
            'RETURN'      => 'De cualquier manera, haz clic en <a href="{{url}}"> aquí </a> para volver a la página principal.'
        ],

        'CONFIG' => [
            'TITLE'       => '¡Problema de configuración del Servidor!',
            'DESCRIPTION' => 'Algunos requisitos de configuración de Servidor no se han cumplido.',
            'DETAIL'      => 'Algo no está bien aquí.',
            'RETURN'      => 'Corrije los siguientes errores, luego <a href="{{url}}"> recargue </a>.'
        ],

        'DESCRIPTION' => 'Hemos sentido una gran perturbación en la Fuerza.',
        'DETAIL'      => 'Esto es lo que tenemos:',

        'ENCOUNTERED' => 'Uhhh ... sucedió algo. No sabemos qué.',

        'MAIL' => 'Error fatal al intentar enviar correo, ponte en contacto con el administrador del servidor. Si tú eres el administrador, comprueba el log de errores.',

        'RETURN' => 'Haz clic en <a href="{{url}}"> aquí </a> para volver a la página principal.',

        'SERVER' => '¡Vaya! Parece que nuestro servidor pudo haber metido la pata. Si eres un administrador, comprueba los registros de errores de PHP o el log de UserFrosting.',

        'TITLE' => 'Perturbación en la Fuerza',
    ]
];
