<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * French message token translations for the 'core' sprinkle.
 *
 * @author Louis Charette
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'Erreur',

        '400' => [
            'TITLE'       => 'Erreur 400: Mauvaise requête',
            'DESCRIPTION' => "Ce n'est probablement pas de votre faute.",
        ],

        '404' => [
            'TITLE'       => 'Erreur 404: Page introuvable',
            'DESCRIPTION' => 'Nous ne pouvons trouver ce que vous cherchez.',
            'DETAIL'      => 'Nous avons tout tenté...',
            'EXPLAIN'     => 'Nous ne pouvons trouver la page que vous cherchez.',
            'RETURN'      => 'Cliquez <a href="{{url}}">ici</a> pour retourner à la page d\'accueil.'
        ],

        'CONFIG' => [
            'TITLE'       => 'Problème de configuration UserFrosting!',
            'DESCRIPTION' => "Les exigences de configuration de UserFrosting n'ont pas été satisfaites.",
            'DETAIL'      => 'Quelque chose cloche ici...',
            'RETURN'      => 'Corrigez les erreurs suivantes, ensuite <a href="{{url}}"> recharger la page</a>.'
        ],

        'DESCRIPTION' => 'Nous avons ressenti un grand bouleversement de la Force.',
        'DETAIL'      => 'Voici les détails :',

        'ENCOUNTERED' => "D'oh! Quelque chose s'est produit. Aucune idée c'est quoi.",

        'MAIL' => "Erreur fatale lors de l'envoie du courriel. Contactez votre administrateur. Si vous être administrateur, consultez les logs.",

        'RETURN' => 'Cliquez <a href="{{url}}">ici</a> pour retourner à la page d\'accueil.',

        'SERVER' => "Oops, il semblerait que le serveur a gaffé. Si vous êtes administrateur, s-v-p vérifier les logs d'erreurs PHP ou ceux de UserFrosting.",

        'TITLE' => 'Bouleversement de la Force'
    ]
];
