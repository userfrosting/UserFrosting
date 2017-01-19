<?php

/**
 * en_US
 *
 * US English message token translations for the 'admin' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

return [
    "AUTH_HOOK_CHAR_LIMIT" => "Le nom des hooks d'autorisation doivent faire entre {{min}} et {{max}} caractères",

    "CANNOT_DELETE_GROUP" => "Le groupe '{{name}}' ne peut pas être supprimé",
    "CONFIG_PLUGIN_INVALID" => "Vous essayez de changer la configuration du plugin '{{plugin}}', mais il n'existe pas.",
    "CONFIG_SETTING_INVALID" => "Vous essayez de changer le réglage '{{name}}' du plugin '{{plugin}}', mais il n'existe pas.",
    "CONFIG_NAME_CHAR_LIMIT" => "Le nom du site doit faire entre {{min}} et {{max}} caractères",
    "CONFIG_URL_CHAR_LIMIT" => "L'URL du site doit faire entre {{min}} et {{max}} caractères",
    "CONFIG_EMAIL_CHAR_LIMIT" => "L'adresse e-mail du site doit faire entre {{min}} et {{max}} caractères",
    "CONFIG_TITLE_CHAR_LIMIT" => "Le nouveau titre de l'utilisateur doit faire entre {{min}} et {{max}} caractères",
    "CONFIG_ACTIVATION_TRUE_FALSE" => "L'activation par e-mail doit être soit `true` ou `false`",
    "CONFIG_REGISTRATION_TRUE_FALSE" => "L'inscription doit être soit `true` ou `false`",
    "CONFIG_ACTIVATION_RESEND_RANGE" => "La durée d'activation doit être entre {{min}} et {{max}} heures",
    "CONFIG_EMAIL_INVALID" => "L'adresse e-mail entrée est invalide",
    "CONFIG_UPDATE_SUCCESSFUL" => "La configuration du site a été mise à jour.",
    "CREATION_COMPLETE" => "Le compte '{{user_name}}' a été créé avec succès.",

    "DELETE_MASTER" => "Vous ne pouvez pas supprimer le compte principal !",
    "DELETION_SUCCESSFUL" => "L'utilisateur '{{user_name}}' a été supprimé avec succès.",
    "DETAILS_UPDATED" => "Les détails du compte de '{{user_name}}' ont été mis à jour",
    "DISABLE_MASTER" => "Vous ne pouvez pas désactiver le compte principal !",
    "DISABLE_SUCCESSFUL" => "Le compte de l'utilisateur '{{user_name}}' a été désactivé avec succès.",
    "DISPLAYNAME_UPDATED" => "Le nom de {{user_name}} a été changé en '{{display_name}}'",

    "ENABLE_SUCCESSFUL" => "Le compte de l'utilisateur '{{user_name}}' a été activé avec succès.",

    "GROUP_ADDED" => "Utilisateur ajouté au groupe '{{name}}'.",
    "GROUP_REMOVED" => "Utilisateur supprimé du groupe '{{name}}'.",
    "GROUP_NOT_MEMBER" => "L'utilisateur n'est pas membre du groupe '{{name}}'.",
    "GROUP_ALREADY_MEMBER" => "L'utilisateur est déjà membre du groupe '{{name}}'.",
    "GROUP_INVALID_ID" => "Le groupe demandé n'existe pas",
    "GROUP_NAME_CHAR_LIMIT" => "Le nom des groupes doit faire entre {{min}} et {{max}} caractères",
    "GROUP_NAME_IN_USE" => "Le nom de groupe '{{name}}' est déjà pris",
    "GROUP_DELETION_SUCCESSFUL" => "Le groupe '{{name}}' a été supprimé avec succès",
    "GROUP_CREATION_SUCCESSFUL" => "Le groupe '{{name}}' a été créé avec succès",
    "GROUP_UPDATE" => "Les détails du groupe '{{name}}' ont été mis à jour avec succès.",
    "GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "Le groupe '{{name}}' ne peut pas être supprimé car il correspond au groupe par défaut des nouveaux utilisateurs.",
    "GROUP_AUTH_EXISTS" => "Le groupe '{{name}}' a déjà une règle configurée pour le hook '{{hook}}'.",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "La règle du hook '{{hook}}' a été créée pour le groupe '{{name}}'.",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "La règle autorisant l'accès au groupe '{{name}}' pour le hook '{{hook}}' a été mise à jour avec succès.",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "La règle autorisant l'accès au groupe '{{name}}' pour le hook '{{hook}}' a été supprimée avec succès.",
    "GROUP_DEFAULT_PRIMARY_NOT_DEFINED" => "Vous ne pouvez pas créer de nouvel utilisateur parce qu'il y a aucun groupe par défaut de défini. Veuillez vérifier vos paramètres de groupes.",

    "INSTALLER_INCOMPLETE" => "Vous ne pouvez pas créer de compte root tant que l'installation n'est pas terminée !",

    "MANUALLY_ACTIVATED" => "Le compte de {{user_name}} a été activé manuellement",
    "MASTER_ACCOUNT_EXISTS" => "Le compte principal existe déjà !",

    "ACTIVITY" => [
        1 => "Activité",
        2 => "Activités"
    ],

    "ADMIN" => [
        "PANEL" => "Panneau admin"
    ],

    "CACHE" => [
        "CLEAR" => "Vider le cache",
        "CLEARED" => "Cache cleared successfully !"
    ],

    "DASHBOARD" => "Tableau de bord",

    "GROUP" => [
        1 => "Groupe",
        2 => "Groupes"
    ],

    "PERMISSION" => [
        1 => "Permission",
        2 => "Permissions"
    ],

    "ROLE" => [
        1 => "Rôle",
        2 => "Rôles"
    ],

    "SYSTEM_INFO" => [
        "@TRANSLATE" => "Informations sur le système",

        "DB_NAME"       => "Base de donnée",
        "DB_VERSION"    => "Version DB",
        "DIRECTORY"     => "Répertoire du projet",
        "PHP_VERSION"   => "Version de PHP",
        "SERVER"        => "Logiciel server",
        "SPRINKLES"     => "Sprinkles chargés",
        "UF_VERSION"    => "Version de UserFrosting",
        "URL"           => "Url racine"
    ],

    "USER" => [
        1 => "Utilisateur",
        2 => "Utilisateurs",

        "LATEST" => "Derniers utilisateurs",
        "VIEW_ALL" => "Voir tous les utilisateurs"
    ],
    "X_USER" => [
        0 => "Aucun utilisateur",
        1 => "{{plural}} utilisateur",
        2 => "{{plural}} utilisateurs"
    ]
];