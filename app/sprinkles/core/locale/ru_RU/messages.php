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
    '@PLURAL_RULE' => 1,

    'ABOUT' => 'О нас',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Капча',
        'FAIL'         => 'Код безопасности был введен с ошибками.',
        'SPECIFY'      => 'Введите код капчи',
        'VERIFY'       => 'Проверьте капчу'
    ],

    'CSRF_MISSING' => 'Отсутствует CSRF токен.  Попробуйте обновить страницу и повторить попытку ещё раз?',

    'DB_INVALID'    => 'Не удается подключиться к базе данных.  Если вы являетесь администратором, пожалуйста проверьте лог ошибок.',
    'DESCRIPTION'   => 'Описание',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Скачать',
        'CSV'          => 'Скачать CSV'
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Ваш e-mail'
    ],

    'HOME'  => 'Главная',

    'LEGAL' => [
        '@TRANSLATION' => 'Правовая информация',
        'DESCRIPTION'  => 'Наша правовая политика применима к использованию вами данного веб-сайта и наших услуг.'
    ],

    'LOCALE' => [
        '@TRANSLATION' => 'Язык'
    ],

    'NAME'       => 'Имя',
    'NAVIGATION' => 'Навигация',
    'NO_RESULTS' => 'Извини, здесь ничего нет.',

    'PAGINATION' => [
        'GOTO' => 'Перейти к странице',
        'SHOW' => 'Показать',

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'   => '{startRow} к {endRow} из {filteredRows} ({totalRows})',
        'NEXT'     => 'Следующая',
        'PREVIOUS' => 'Предыдущая',
        'FIRST'    => 'Первая',
        'LAST'     => 'Последняя'
    ],
    'PRIVACY' => [
        '@TRANSLATION' => 'Политика конфиденциальности',
        'DESCRIPTION'  => 'Наша политика конфиденциальности описывает, какую информацию мы собираем от вас и как мы будем использовать её.'
    ],

    'SLUG'           => 'Метка',
    'SLUG_CONDITION' => 'Метка/Условия',
    'SLUG_IN_USE'    => '<strong>{{slug}}</strong> метка уже существует',
    'STATUS'         => 'Статус',
    'SUGGEST'        => 'Предложить',

    'UNKNOWN' => 'Неизвестно',

    // Actions words
    'ACTIONS'                  => 'Действия',
    'ACTIVATE'                 => 'Активировать',
    'ACTIVE'                   => 'Активные',
    'ADD'                      => 'Добавить',
    'CANCEL'                   => 'Отмена',
    'CONFIRM'                  => 'Подтвердить',
    'CREATE'                   => 'Создать',
    'DELETE'                   => 'Удалить',
    'DELETE_CONFIRM'           => 'Вы уверены, что хотите удалить это?',
    'DELETE_CONFIRM_YES'       => 'Да, удалить',
    'DELETE_CONFIRM_NAMED'     => 'Вы уверены, что хотите удалить {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Да, удалить {{name}}',
    'DELETE_CANNOT_UNDONE'     => 'Это действие нельзя будет отменить.',
    'DELETE_NAMED'             => 'Удаление {{name}}',
    'DENY'                     => 'Запретить',
    'DISABLE'                  => 'Отключить',
    'DISABLED'                 => 'Отключено',
    'EDIT'                     => 'Изменить',
    'ENABLE'                   => 'Включить',
    'ENABLED'                  => 'Включено',
    'OVERRIDE'                 => 'Отменить',
    'RESET'                    => 'Сброс',
    'SAVE'                     => 'Сохранить',
    'SEARCH'                   => 'Поиск',
    'SORT'                     => 'Сортировка',
    'SUBMIT'                   => 'Отправить',
    'PRINT'                    => 'Печать',
    'REMOVE'                   => 'Удалить',
    'UNACTIVATED'              => 'Не активировано',
    'UPDATE'                   => 'Обновить',
    'YES'                      => 'Да',
    'NO'                       => 'Нет',
    'OPTIONAL'                 => 'Дополнительно',

    // Misc.
    'BUILT_WITH_UF'     => 'Создано через <a href="http://www.userfrosting.com"> UserFrosting</a>',
    'ADMINLTE_THEME_BY' => 'Тема от <strong><a href="http://almsaeedstudio.com"> Almsaeed Studio</a>.</strong> Все права защищены',
    'WELCOME_TO'        => 'Добро пожаловать на {{title}}!'
];
