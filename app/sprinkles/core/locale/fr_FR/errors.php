<?php

/**
 * fr_FR
 *
 * French message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Louis Charette
 */

return [
    "ERROR" => [
        "@TRANSLATION" => "Erreur",

        "TITLE" => "Bouleversement de la Force",
        "DESCRIPTION" => "Nous avons ressenti un grand bouleversement de la Force.",
        "ENCOUNTERED" => "D'oh! Quelque chose s'est produit. Aucune idée c'est quoi.",
        "DETAIL" => "Voici les détails :",
        "RETURN" => 'Cliquez <a href="{{url}}">ici</a> pour retourner à la page d\'accueil.',

        "400" => [
            "TITLE" => "Erreur 400: Mauvaise requête",
            "DESCRIPTION" => "Ce n'est probablement pas de votre faute.",
        ],

        "404" => [
            "TITLE" => "Erreur 404: Page introuvable",
            "DESCRIPTION" => "Nous ne pouvons trouver ce que vous cherchez.",
            "DETAIL" => "Nous avons tout tenté...",
            "EXPLAIN" => "Nous ne pouvons trouver la page que vous cherchez.",
            "RETURN" => 'Cliquez <a href="{{url}}">ici</a> pour retourner à la page d\'acceuil.'
        ],

        "CONFIG" => [
            "TITLE" => "Problème de configuration UserFrosting!",
            "DESCRIPTION" => "Les exigences de configuration de UserFrosting n'ont pas été satisfaites.",
            "DETAIL" => "Quelque chose cloche ici...",
            "RETURN" => 'Corrigez les erreurs suivantes, ensuite <a href="{{url}}"> recharger la page</a>.'
        ]
    ]
];
