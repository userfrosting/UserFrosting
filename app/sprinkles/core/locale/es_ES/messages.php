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
    '@PLURAL_RULE' => 1,

    'ABOUT'      => 'Acerca de',
    'WELCOME_TO' => '¡Bienvenido a {{title}}!',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Captcha',
        'FAIL'         => 'No has introducido correctamente el código de captcha.',
        'SPECIFY'      => 'Introduzce el captcha',
        'VERIFY'       => 'Verificar el captcha'
    ],

    'CSRF_MISSING' => '¿Falta el símbolo CSRF?. Intenta refrescar la página y luego volver a enviarla',

    'DB_INVALID'    => 'No se puede conectar a la base de datos. Si eres un administrador, comprueba su registro de errores.',
    'DESCRIPTION'   => 'Descripción',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Descargar',
        'CSV'          => 'Descargar CSV'
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Tu correo electrónico'
    ],

    'HOME'  => 'Inicio',

    'LEGAL' => 'Política Legal',

    'LOCALE' => [
        '@TRANSLATION' => 'Traducción'
    ],

    'MAIL_ERROR' => 'Error fatal al intentar enviar correo, ponte en contacto con el administrador del servidor. Si eres el administrador, comprueba el registro de correo de UF.',

    'NAME'       => 'Nombre',
    'NAVIGATION' => 'Navegación',

    'PAGINATION' => [
        'GOTO' => 'Ir a la página',
        'SHOW' => 'Mostrar',

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'   => '{startRow} a {endRow} de {filteredRows} ({totalRows})',
        'NEXT'     => 'Siguiente página',
        'PREVIOUS' => 'Pagina anterior',
        'FIRST'    => 'Primera página',
        'LAST'     => 'Última página'
    ],
    'PRIVACY' => 'Política de privacidad',

    'SLUG'           => 'Slug',
    'SLUG_CONDITION' => 'Slug/Condiciones',
    'SLUG_IN_USE'    => 'A <strong>{{slug}}</strong> slug ya existe',
    'STATUS'         => 'Estado',
    'SUGGEST'        => 'Sugerencia',

    'UNKNOWN' => 'Desconocido',

    // Actions words
    'ACTIONS'                  => 'Acciones',
    'ACTIVATE'                 => 'Activar',
    'ACTIVE'                   => 'Activo',
    'ADD'                      => 'Añadir',
    'CANCEL'                   => 'Cancelar',
    'CONFIRM'                  => 'Confirmar',
    'CREATE'                   => 'Crear',
    'DELETE'                   => 'Eliminar',
    'DELETE_CONFIRM'           => '¿Estás seguro que quieres eliminar esto?',
    'DELETE_CONFIRM_YES'       => 'Sí, borrar',
    'DELETE_CONFIRM_NAMED'     => '¿Seguro que quieres eliminar {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Sí, eliminar {{nombre}}',
    'DELETE_CANNOT_UNDONE'     => 'Esta acción no se puede deshacer.',
    'DELETE_NAMED'             => 'Eliminar {{name}}',
    'DENY'                     => 'Negar',
    'DISABLE'                  => 'Inhabilitar',
    'DISABLED'                 => 'Deshabilidato',
    'EDIT'                     => 'Editar',
    'ENABLE'                   => 'Habilitar',
    'ENABLED'                  => 'Habilitado',
    'OVERRIDE'                 => 'Anular',
    'RESET'                    => 'Reiniciar',
    'SAVE'                     => 'Guardar',
    'SEARCH'                   => 'Buscar',
    'SORT'                     => 'Ordenar',
    'SUBMIT'                   => 'Enviar',
    'PRINT'                    => 'Imprimir',
    'REMOVE'                   => 'Remover',
    'UNACTIVATED'              => 'Desactivado',
    'UPDATE'                   => 'Actualizar',
    'YES'                      => 'Sí',
    'NO'                       => 'No',
    'OPTIONAL'                 => 'Opcional',

    // Misc.
    'BUILT_WITH_UF'     => 'Construido con <a href="http://www.userfrosting.com"> UserFrosting </a>',
    'ADMINLTE_THEME_BY' => 'Theme by <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights reserved'
];
