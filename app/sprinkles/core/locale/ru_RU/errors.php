<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Russian message token translations for the 'core' sprinkle.
 *
 * @author @rendername
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'Ошибка',

        '400' => [
            'TITLE'       => 'Ошибка 400: Неправильный запрос',
            'DESCRIPTION' => 'Это, вероятно, не ваша вина.',
        ],

        '404' => [
            'TITLE'       => 'Ошибка 404: Не найдено',
            'DESCRIPTION' => 'Кажется, мы не можем найти то, что вам нужно.',
            'DETAIL'      => 'Мы пытались найти вашу страницу...',
            'EXPLAIN'     => 'Мы не можем найти страницу, которую вы искали.',
            'RETURN'      => 'В любом случае, нажмите <a href="{{url}}"> здесь</a> чтобы вернуться на главную страницу.'
        ],

        'CONFIG' => [
            'TITLE'       => 'Проблема в конфигурации!',
            'DESCRIPTION' => 'Некоторые требования к конфигурации UserFrosting, не были соблюдены.',
            'DETAIL'      => 'Что-то здесь не так.',
            'RETURN'      => 'Пожалуйста, исправьте следующие ошибки, затем <a href="{{url}}"> перезагрузите</a>.'
        ],

        'DESCRIPTION' => 'Мы обнаружили большое и сильное нарушение.',
        'DETAIL'      => 'Вот что мы получили:',

        'ENCOUNTERED' => 'Ох... что-то произошло.  Мы не знаем, что.',

        'MAIL' => 'Неустранимая ошибка почтовой службы, обратитесь к администратору сервера.  Если вы являетесь администратором, пожалуйста, проверьте логи.',

        'RETURN' => 'Нажмите <a href="{{url}}"> здесь</a> для возврата на главную страницу.',

        'SERVER' => 'К сожалению, кажется сервер имеет ошибки. Если вы являетесь администратором сервера, пожалуйста проверьте логи.',

        'TITLE' => 'Сильное нарушение'
    ]
];
