<?php

$header = 'UserFrosting (http://www.userfrosting.com)

@link      https://github.com/userfrosting/UserFrosting
@copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
@license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)';

$rules = [
    'header_comment' => [
        'header'       => $header,
    ]
];
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app/src',
        __DIR__ . '/app/tests',
        __DIR__ . '/public'
    ]);
$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRiskyAllowed(true);
