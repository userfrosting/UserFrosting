<?php

/**
 * fr_FR
 *
 * FR French generic message translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 * @translator Louis Charette
 */

return [
    "@PLURAL_RULE" => 2,

    "ABOUT" => "À propos",

	"CAPTCHA" => [
	    "@TRANSLATE" => "Captcha",
        "VERIFY" => "Vérification du captcha",
        "SPECIFY" => "Entrer la valeur du captcha",
        "FAIL" => "La valeur du captcha n'a pas été entrée correctement."
    ],

    "CSRF_MISSING" => "Jeton CSRF manquant. Essayez de rafraîchir la page et de soumettre de nouveau?",

    "DB_INVALID" => "Impossible de se connecter à la base de données. Si vous êtes un administrateur, vérifiez votre journal d'erreurs.",

    "EMAIL" => [
        "@TRANSLATION" => "Email"
    ],

	"FEATURE_DISABLED" => "Cette fonction est présentement désactivée",

    "LOCALE" => "Langue",

    "MAIL_ERROR" => "Erreur fatale lors de l'envoie du courriel. Contactez votre administrateur. Si vous être administrateur, consultez les logs.",

    // Actions words
    "ACTIONS" => "Actions",
    "ADD" => "Ajouter",
    "CANCEL" => "Annuler",
    "CONFIRM" => "Confirmer",
    "CREATE" => "Créer",
    "DELETE" => "Supprimer",
    "DELETE_CONFIRM" => "Êtes-vous sûr de vouloir supprimer ceci?",
    "DELETE_CONFIRM_YES" => "Oui, supprimer",
    "DELETE_CONFIRM_NAMED" => "Êtes-vous sûr de vouloir supprimer {{name}}?",
    "DELETE_CONFIRM_YES_NAMED" => "Oui, supprimer {{name}}",
    "DELETE_CANNOT_UNDONE" => "Cette action ne peut être annulée.", //This action cannot be undone
    "DELETE_NAMED" => "Supprimer {{name}}",
    "DENY" => "Refuser",
    "EDIT" => "Modifier",
    "RESET" => "Réinitialiser",
    "SAVE" => "Sauvegarder",
    "SORT" => "Trier",
    "PRINT" => "Imprimer",
    "UPDATE" => "Mettre à jour",

    // Misc.
    "BUILT_WITH_UF" => "Créé avec <a href=\"http://www.userfrosting.com\">UserFrosting</a>.",

    // TOOLS
    "_LINK" => "<a {{link_attributes}}>{{link_text}}</a>"
];
