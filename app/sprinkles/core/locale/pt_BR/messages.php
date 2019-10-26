<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'core' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    '@PLURAL_RULE' => 1,

    'ABOUT' => 'Sobre',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Captcha',
        'FAIL'         => 'Você não inseriu o código do captcha corretamente.',
        'SPECIFY'      => 'Insira o captcha',
        'VERIFY'       => 'Verifique o captcha',
    ],

    'CSRF_MISSING' => 'Está faltando o token CSRF.  Tente atualizar a página e então submeter novamente?',

    'DB_INVALID'    => 'Não é possível conectar ao banco de dados.  Se você for um administrador, por favor, verifique o seu log de erros.',
    'DESCRIPTION'   => 'Descrição',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Download',
        'CSV'          => 'Download CSV',
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Seu endereço de email',
    ],

    'HOME'  => 'Home',

    'LEGAL' => [
        '@TRANSLATION' => 'Política Legal',
        'DESCRIPTION'  => 'Nossa política legal se aplica para o seu uso deste website e nossos serviços.',
    ],

    'LOCALE' => [
        '@TRANSLATION' => 'Locale',
    ],

    'NAME'       => 'Nome',
    'NAVIGATION' => 'Navegação',
    'NO_RESULTS' => "Desculpe, não temos nada aqui.",

    'PAGINATION' => [
        'GOTO' => 'Pular para página',
        'SHOW' => 'Mostrar',

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'   => '{startRow} para {endRow} de {filteredRows} ({totalRows})',
        'NEXT'     => 'Próxima página',
        'PREVIOUS' => 'Página anterior',
        'FIRST'    => 'Primeira Página',
        'LAST'     => 'Última página',
    ],
    'PRIVACY' => [
        '@TRANSLATION' => 'Política Legal',
        'DESCRIPTION'  => 'Nossa política legal descreve que tipo de informação nós coletamos de você e como nós iremos usa-la.',
    ],

    'SLUG'           => 'Slug',
    'SLUG_CONDITION' => 'Slug/Condições',
    'SLUG_IN_USE'    => 'A <strong>{{slug}}</strong> slug já existe',
    'STATUS'         => 'Status',
    'SUGGEST'        => 'Sugerir',

    'UNKNOWN' => 'Desconhecido',

    // Actions words
    'ACTIONS'                  => 'Ações',
    'ACTIVATE'                 => 'Ativar',
    'ACTIVE'                   => 'Ativo(a)',
    'ADD'                      => 'Adicionar',
    'CANCEL'                   => 'Cancelar',
    'CONFIRM'                  => 'Confirme',
    'CREATE'                   => 'Criar',
    'DELETE'                   => 'Deletar',
    'DELETE_CONFIRM'           => 'Você tem certeza que deseja deletar isto??',
    'DELETE_CONFIRM_YES'       => 'Sim, deletar',
    'DELETE_CONFIRM_NAMED'     => 'Você tem certeza que deseja deletar {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Sim, deletar {{name}}',
    'DELETE_CANNOT_UNDONE'     => 'Esta ação não pode ser desfeita.',
    'DELETE_NAMED'             => 'Deletar {{name}}',
    'DENY'                     => 'Rejeitar',
    'DISABLE'                  => 'Desativar',
    'DISABLED'                 => 'Desativado(a)',
    'EDIT'                     => 'Editar',
    'ENABLE'                   => 'Ativar',
    'ENABLED'                  => 'Ativo(a)',
    'OVERRIDE'                 => 'Sobrescrever',
    'RESET'                    => 'Resetar',
    'SAVE'                     => 'Salvar',
    'SEARCH'                   => 'Procurar',
    'SORT'                     => 'Ordenar',
    'SUBMIT'                   => 'Enviar',
    'PRINT'                    => 'Imprimir',
    'REMOVE'                   => 'Remover',
    'UNACTIVATED'              => 'Inativado(a)',
    'UPDATE'                   => 'Atualizar',
    'YES'                      => 'Sim',
    'NO'                       => 'Não',
    'OPTIONAL'                 => 'Opcional',

    // Misc.
    'BUILT_WITH_UF'     => 'Construído com <a href="http://www.userfrosting.com">UsuárioFrosting</a>',
    'ADMINLTE_THEME_BY' => 'Tema por <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> Todos os direitos reservados',
    'WELCOME_TO'        => 'Bem vindo ao {{title}}!',
];
