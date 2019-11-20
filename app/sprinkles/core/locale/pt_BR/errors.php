<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Brazilian Portuguese message token translations for the 'core' sprinkle.
 *
 * @author Maxwell Kenned (kenned123@gmail.com)
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'Erro',

        '400' => [
            'TITLE'       => 'Erro 400: Pedido Inválido',
            'DESCRIPTION' => 'Provavelmente a culpa não é sua.',
        ],

        '404' => [
            'TITLE'       => 'Erro 404: Página não Encontrada',
            'DESCRIPTION' => 'Parece que não conseguimos encontrar a página que procura.',
            'DETAIL'      => 'Tentamos encontrar a sua página...',
            'EXPLAIN'     => 'Não conseguimos encontrar a página que procura.',
            'RETURN'      => 'De qualquer forma, clique <a href="{{url}}">aqui</a> para voltar à página inicial.',
        ],

        'CONFIG' => [
            'TITLE'       => 'Problema de Configuração do UserFrosting!',
            'DESCRIPTION' => 'Alguns requisitos de configuração do UserFrosting não foram satisfeitos.',
            'DETAIL'      => 'Algo não está certo.',
            'RETURN'      => 'Por favor corrija os seguintes erros, depois <a href="{{url}}">atualize</a> a página.',
        ],

        'DESCRIPTION' => 'Sentimos uma grande pertubação na Força.',
        'DETAIL'      => 'Aqui está o que temos:',
        'ENCOUNTERED' => 'Uhhh...algo aconteceu. Não sabemos bem o quê.',
        'MAIL'        => 'Erro fatal ao tentar enviar email, contate o administrator do servidor. Se é administrador, por favor consulte o log de email do UserFrosting.',
        'RETURN'      => 'Clique <a href="{{url}}">aqui</a> para voltar à página inicial.',
        'SERVER'      => 'Ops, parece que nosso servidor pode ter sido enganado. Se você é um administrador, verifique os registros PHP ou UserFrosting.',
        'TITLE'       => 'Perturbação na força',
    ],
];
