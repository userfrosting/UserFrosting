<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Portuguese Brazil message token translations for the 'core' sprinkle.
 *
 * @author Alan Naidon
 */

return [
    'ERROR' => [
        '@TRANSLATION' => 'Erro',

        '400' => [
            'TITLE'       => 'Erro 400: Problema na requisição',
            'DESCRIPTION' => 'Provavelmente não é sua falta.',
        ],

        '404' => [
            'TITLE'       => 'Erro 404: Não encontrado',
            'DESCRIPTION' => 'Parece que não conseguimos encontrar o que você estava procurando.',
            'DETAIL'      => 'Nós tentamos encontrar sua página...',
            'EXPLAIN'     => 'Nós não conseguimos encontrar a página que você estava procurando.',
            'RETURN'      => 'De qualquer forma, clique <a href="{{url}}">aqui</a> para retornar a página inicial.',
        ],

        'CONFIG' => [
            'TITLE'       => 'Problema na configuração do UsuárioFrosting!',
            'DESCRIPTION' => 'Alguns requisitos de configuração do UsuárioFrosting não foram satisfeitas.',
            'DETAIL'      => 'Alguma coisa não está certa aqui.',
            'RETURN'      => 'Por favor, corrija os segintes erros, e então <a href="{{url}}">recarregue</a>.',
        ],

        'DESCRIPTION' => 'Sentimos uma grande perturbação na Força.',
        'DETAIL'      => 'Aqui está o que temos::',

        'ENCOUNTERED' => 'Uhhh...alguma coisa aconteceu. Não sabemos o que.',

        'MAIL' => 'Erro fatal ao tentar enviar email, contate o administrador do seu servidor. Se você for o admin, por favor, verifique o log do UsuárioFrosting..',

        'RETURN' => 'Clique <a href="{{url}}">aqui</a> para retornar para página inicial.',

        'SERVER' => 'Oops, parece que nosso servidor pode ter cometido algum erro. Se você for um admin, por favor, verifique o PHP ou os logs do UsuárioFrosting.',

        'TITLE' => 'Perturbação na força',
    ],
];
