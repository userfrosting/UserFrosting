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
    '@PLURAL_RULE' => 2,

    'ABOUT' => 'À propos',

    'CAPTCHA' => [
        '@TRANSLATE' => 'Captcha',
        'VERIFY'     => 'Vérification du captcha',
        'SPECIFY'    => 'Entrer la valeur du captcha',
        'FAIL'       => "La valeur du captcha n'a pas été entrée correctement."
    ],

    'CSRF_MISSING' => 'Jeton CSRF manquant. Essayez de rafraîchir la page et de soumettre de nouveau?',

    'DB_INVALID'    => "Impossible de se connecter à la base de données. Si vous êtes un administrateur, vérifiez votre journal d'erreurs.",
    'DESCRIPTION'   => 'Description',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Télécharger',
        'CSV'          => 'Télécharger CSV'
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Votre adresse email'
    ],

    'HOME'  => 'Accueil',

    'LEGAL' => 'Politique légale',

    'LOCALE' => [
        '@TRANSLATION' => 'Langue'
    ],

    'NAME'       => 'Nom',
    'NAVIGATION' => 'Menu principal',
    'NO_RESULTS' => 'Aucun résultat trouvé.',

    'PAGINATION' => [
        'GOTO'   => 'Aller à la page',
        'SHOW'   => 'Afficher',
        'OUTPUT' => '{startRow} à {endRow} de {filteredRows} ({totalRows})'
    ],
    'PRIVACY' => 'Politique de confidentialité',

    'SLUG'           => 'Jeton',
    'SLUG_CONDITION' => 'Jeton/Conditions',
    'SLUG_IN_USE'    => 'Un jeton <strong>{{slug}}</strong> existe déjà',
    'STATUS'         => 'Statut',
    'SUGGEST'        => 'Suggérer',

    'UNKNOWN' => 'Inconnu',

    // Actions words
    'ACTIONS'                  => 'Actions',
    'ACTIVATE'                 => 'Autoriser',
    'ACTIVE'                   => 'Activé',
    'ADD'                      => 'Ajouter',
    'CANCEL'                   => 'Annuler',
    'CONFIRM'                  => 'Confirmer',
    'CREATE'                   => 'Créer',
    'DELETE'                   => 'Supprimer',
    'DELETE_CONFIRM'           => 'Êtes-vous sûr de vouloir supprimer ceci?',
    'DELETE_CONFIRM_YES'       => 'Oui, supprimer',
    'DELETE_CONFIRM_NAMED'     => 'Êtes-vous sûr de vouloir supprimer {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Oui, supprimer {{name}}',
    'DELETE_CANNOT_UNDONE'     => 'Cette action ne peut être annulée.', //This action cannot be undone
    'DELETE_NAMED'             => 'Supprimer {{name}}',
    'DENY'                     => 'Refuser',
    'DISABLE'                  => 'Désactiver',
    'DISABLED'                 => 'Désactivé',
    'EDIT'                     => 'Modifier',
    'ENABLE'                   => 'Activer',
    'ENABLED'                  => 'Activé',
    'OVERRIDE'                 => 'Forcer',
    'RESET'                    => 'Réinitialiser',
    'SAVE'                     => 'Sauvegarder',
    'SEARCH'                   => 'Rechercher',
    'SORT'                     => 'Trier',
    'SUBMIT'                   => 'Envoyer',
    'PRINT'                    => 'Imprimer',
    'REMOVE'                   => 'Supprimer',
    'UNACTIVATED'              => 'Non activé',
    'UPDATE'                   => 'Mettre à jour',
    'YES'                      => 'Oui',
    'NO'                       => 'Non',
    'OPTIONAL'                 => 'Facultatif',

    // Misc.
    'BUILT_WITH_UF'     => 'Créé avec <a href="http://www.userfrosting.com">UserFrosting</a>',
    'ADMINLTE_THEME_BY' => 'Thème par <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> Tous droits réservés'
];
