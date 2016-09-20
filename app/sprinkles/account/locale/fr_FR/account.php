<?php

/**
 * fr_FR
 *
 * FR French account message translations for the account sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Ulysse Ramage
 * @author Louis Charette
 */

/*
{{name}} - Dynamic markers which are replaced at run time by the relevant index.
*/

return [
    //Common stuff shared by all Sprinkles
    "USERNAME" => "Nom d\'utilisateur",
    "PASSWORD" => "Mot de passe",
    "FIRST_NAME" => "Prénom",
    "LAST_NAME" => "Nom",
    "EMAIL" => "Courriel",

    "ACCOUNT" => [
        "@TRANSLATE" => "Profil",

        "ACTION_SEND_AGAIN" => "Réenvoyer le courriel d'activation",

        "LOGIN" => "Connexion",
        "LOGIN_SOCIAL" => "Connexion avec",
        "LOGOUT" => "Déconnexion",

        "REGISTER" => "S'enregistrer",
        "REGISTER_ME" => "S'enregistrer",

        "PASSWORD_CONFIRM" => "Confirmer le mot de passe",
        "PASSWORD_BETWEEN" => "Mot de passe (entre {{min}}-{{max}} caratères)",
        "PASSWORD_FORGET" => "Mot de passe oublié",

        "SIGNIN" => "Se connecter",
        "SIGNIN_OR_REGISTER" => "Se connecter ou s'inscrire",

        "REGISTER" => "Créer un profil",

        "REMEMBER_ME" => "Se souvenir de moi",
        "REMEMBER_ME_ON_COMPUTER" => "Se souvenir de moi sur cet ordinateur (non recommandé pour les ordinateurs publics)",

        "CHOOSE_USERNAME" => "Choisir un nom d'utilisateur unique",

        "TOS" => "termes et conditions",
        "TOS_AGREEMENT" => "En s'enregistrant sur {{site_title}}, vous acceptez les {{&_LINK}}.",

        "PAGE" => [
            "LOGIN" => [
                "TITLE" => "Bienvenue !",
                "SUBTITLE" => "Créez un profil gratuitement ou connectez-vous avec un profil existant",
                "DESCRIPTION" => "Se connecter avec son profil ou en créer un nouveau.",
            ]
        ]
    ]
];
