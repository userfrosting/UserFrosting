<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Portuguese message token translations for the 'core' sprinkle.
 *
 * @author Bruno Silva (brunomnsilva@gmail.com)
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
            'DETAIL'      => 'Tentámos encontrar a sua página...',
            'EXPLAIN'     => 'Não conseguimos encontrar a página que procura.',
            'RETURN'      => 'De qualquer forma, clique <a href="{{url}}">aqui</a> para regressar à página inicial.'
        ],

        'CONFIG' => [
            'TITLE'       => 'Problema de Configuração do UserFrosting!',
            'DESCRIPTION' => 'Alguns requisitos de configuração do UserFrosting não foram satisfeitos.',
            'DETAIL'      => 'Algo não está bem.',
            'RETURN'      => 'Por favor corrija os seguintes erros, depois <a href="{{url}}">refresque</a> a página.'
        ],

        'DESCRIPTION' => 'Sentimos uma grande perturbância na Força.',
        'DETAIL'      => 'Eis o que sabemos:',

        'ENCOUNTERED' => 'Uhhh...algo aconteceu.  Não sabemos bem o quê.',

        'MAIL' => 'Erro fatal ao tentar enviar email, contate o administrator do servidor.  Se é administrador, por favor consulte o log de mail do UF.',

        'RETURN' => 'Clique <a href="{{url}}">aqui</a> para regressar à página inicial.',

        'SERVER' => 'Oops, parece que o nosso servidor deu o berro. Se é um administrador, por favor consulte o log de erros PHP ou UF.',

        'TITLE' => 'Perturbância na Força'
    ]
];
