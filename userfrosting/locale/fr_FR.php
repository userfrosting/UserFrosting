<?php
/**
 * fr_FR
 *
 * FR French message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Ulysse Ramage
 */
/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/
$lang = array();
// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "L'inscription est simple et rapide.",
	"MENU_USERS" => "Utilisateurs",
	"MENU_CONFIGURATION" => "Configuration",
	"MENU_SITE_SETTINGS" => "Préférences du site",
	"MENU_GROUPS" => "Groupes",
	"HEADER_MESSAGE_ROOT" => "VOUS ÊTES CONNECTÉ EN TANT QU'ADMINISTRATEUR ROOT"
]);
// Installer
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "Vous ne pouvez pas créer de compte root tant que l'installation n'est pas terminée !",
	"MASTER_ACCOUNT_EXISTS" => "Le compte principal existe déjà !",
	"MASTER_ACCOUNT_NOT_EXISTS" => "Vous ne pouvez pas créer de compte tant que le compte principal n'a pas été enregistré !",
	"CONFIG_TOKEN_MISMATCH" => "Désolé, ce jeton de configuration est incorrect."
));
// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Veuillez entrer votre nom d'utilisateur.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Veuillez entrer votre nom tel qu'il apparaîtra publiquement.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Veuillez entrer votre mot de passe.",
	"ACCOUNT_SPECIFY_EMAIL" => "Veuillez entrer votre adresse e-mail.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Veuillez recopier le code captcha.",
	"ACCOUNT_SPECIFY_LOCALE" => "Veuillez sélectionner une langue valide.",
	"ACCOUNT_INVALID_EMAIL" => "Adresse e-mail invalide",
	"ACCOUNT_INVALID_USERNAME" => "Nom d'utilisateur invalide",
	"ACCOUNT_INVALID_USER_ID" => "L'id d'utilisateur demandé n'existe pas.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Le nom d'utilisateur ou l'adresse e-mail est invalide.",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Le nom d'utilisateur ou le mot de passe est invalide.",
	"ACCOUNT_ALREADY_ACTIVE" => "Votre compte est déjà activé.",
	"ACCOUNT_REGISTRATION_DISABLED" => "Désolé, l'inscription est désactivée.",
	"ACCOUNT_REGISTRATION_LOGOUT" => "Désolé, vous ne pouvez pas vous inscrire en étant connecté. Merci de bien vouloir vous déconnecter.",
	"ACCOUNT_INACTIVE" => "Votre compte est inactif. Veuillez regarder dans le dossier spam de votre boîte mail pour trouver le mail d'activation.",
	"ACCOUNT_DISABLED" => "Ce compte a été désactivé. Contactez-nous pour plus d'informations.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Le nom d'utilisateur doit faire entre {{min}} et {{max}} caractères.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Le nom public doit faire entre {{min}} et {{max}} caractères.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "Le mot de passe doit faire entre {{min}} et {{max}} caractères.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "L'adresse e-mail doit faire entre {{min}} et {{max}} caractères.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Les titres doivent faire entre {{min}} et {{max}} caractères.",
	"ACCOUNT_PASS_MISMATCH" => "Le mot de passe et la confirmation doivent être identiques",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Le nom doit contenir seulement des caractères alphanumériques",
	"ACCOUNT_USERNAME_IN_USE" => "Un compte existe déjà avec le nom d'utilisateur '{{user_name}}'",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Un compte existe déjà avec le nom '{{display_name}}'",
	"ACCOUNT_EMAIL_IN_USE" => "Un compte existe déjà avec l'adresse e-mail '{{email}}'",
	"ACCOUNT_LINK_ALREADY_SENT" => "Un e-mail d'activation vous a déjà été envoyé il y a {{resend_activation_threshold}} seconde(s). Veuillez réessayer plus tard.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "Un nouvel e-mail d'activation vous a été envoyé",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Veuillez entrer votre nouveau mot de passe",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Veuillez confirmer votre mot de passe",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "Le nouveau mot de passe doit faire entre {{min}} et {{max}} caractères",
	"ACCOUNT_PASSWORD_INVALID" => "Le mot de passe actuel ne correspond pas à celui qui a été enregistré",
	"ACCOUNT_DETAILS_UPDATED" => "Les détails du compte de '{{user_name}}' ont été mis à jour",
	"ACCOUNT_CREATION_COMPLETE" => "Le compte '{{user_name}}' a été créé avec succès.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "Votre compte a été activé avec succès. Vous pouvez désormais vous connecter.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Votre compte a été créé avec succès. Vous pouvez désormais vous connecter.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Votre compte a été créé avec succès. Un e-mail d'activation vous a été envoyé à votre adresse e-mail.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Vous ne pouvez pas mettre à jour le même mot de passe",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Merci de confirmer votre mot de passe actuel",
	"ACCOUNT_SETTINGS_UPDATED" => "Les paramètres du compte ont été mis à jour",
	"ACCOUNT_PASSWORD_UPDATED" => "Le mot de passe du compte a été mis à jour",
	"ACCOUNT_EMAIL_UPDATED" => "L'adresse e-mail du compte a été mise à jour",
	"ACCOUNT_TOKEN_NOT_FOUND" => "Ce jeton n'existe pas / Le compte est déjà activé",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "Le nom d'utilisateur doit contenir seulement des caractères alphanumériques",
	"ACCOUNT_DELETE_MASTER" => "Vous ne pouvez pas supprimer le compte principal !",
	"ACCOUNT_DISABLE_MASTER" => "Vous ne pouvez pas désativer le compte principal !",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "Le compte de l'utilisateur '{{user_name}}' a été désactivé avec succès.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "Le compte de l'utilisateur '{{user_name}}' a été activé avec succès.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "L'utilisateur '{{user_name}}' a été supprimé avec succès.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "Le compte de {{user_name}} a été activé manuellement",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "Le nom de {{user_name}} a été changé en '{{display_name}}'",
	"ACCOUNT_TITLE_UPDATED" => "Le titre de {{user_name}} a été changé en '{{title}}'",
	"ACCOUNT_GROUP_ADDED" => "Utilisateur ajouté au groupe '{{name}}'.",
	"ACCOUNT_GROUP_REMOVED" => "Utilisateur supprimé du groupe '{{name}}'.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "L'utilisateur n'est pas membre du groupe '{{name}}'.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "L'utilisateur est déjà membre du groupe '{{name}}'.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Le groupe principal du compte '{{user_name}}' a été changé avec succès.",
	"ACCOUNT_WELCOME" => "Bonjour, {{display_name}}"
));
// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "Le champ '{{self}}' doit être rempli.",
	"VALIDATE_BOOLEAN" => "La valeur de '{{self}}' doit être '0' ou '1'.",
	"VALIDATE_INTEGER" => "La valeur de '{{self}}' doit être un nombre entier.",
	"VALIDATE_ARRAY" => "Les valeurs de '{{self}}' doivent être dans un tableau."
));
// Configuration
$lang = array_merge($lang,array(
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
	"MINIFICATION_SUCCESS" => "Le code CSS et JS a été compressé avec succès."
));
// Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Le jeton d'activation est invalide",
	"FORGOTPASS_OLD_TOKEN" => "Le jeton spécifié a expiré",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Impossible de mettre à jour le mot de passe",
	"FORGOTPASS_NEW_PASS_EMAIL" => "Un e-mail contenant votre nouveau mot de passe vous a été envoyé",
	"FORGOTPASS_REQUEST_CANNED" => "Votre demande de mot de passe a été annulée",
	"FORGOTPASS_REQUEST_EXISTS" => "Il existe déjà une demande de mot de passe pour ce compte",
	"FORGOTPASS_REQUEST_SUCCESS" => "Un e-mail contenant les instructions de réinitialisation de votre mot de passe vous a été envoyé"
));
// Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "Une erreur est survenue lors de l'envoi de l'e-mail, merci de nous contacter si le problème persiste",
));
// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Le cryptage du mot de passe a échoué. Merci de nous contacter si le problème persiste.",
	"NO_DATA" => "Aucune donnée/données corrompues",
	"CAPTCHA_FAIL" => "La question de sécurité est invalide",
	"CONFIRM" => "Confirmer",
	"DENY" => "Refuser",
	"SUCCESS" => "Succès",
	"ERROR" => "Erreur",
	"SERVER_ERROR" => "Oups, une erreur est survenue. Merci de nous contacter si le problème persiste.",
	"NOTHING_TO_UPDATE" => "Rien à mettre à jour",
	"SQL_ERROR" => "Erreur SQL",
	"FEATURE_DISABLED" => "Cette fonctionnalité est désactivée",
	"ACCESS_DENIED" => "Hmm, il semble que vous n'ayez pas le droit de faire ça.",
	"LOGIN_REQUIRED" => "Désolé, vous devez être connecté pour accéder à ce contenu.",
	"LOGIN_ALREADY_COMPLETE" => "Vous êtes déjà connecté !"
));
// Permissions
$lang = array_merge($lang,array(
		"GROUP_INVALID_ID" => "Le groupe demandé n'existe pas",
		"GROUP_NAME_CHAR_LIMIT" => "Le nom des groupes doit faire entre {{min}} et {{max}} caractères",
	"AUTH_HOOK_CHAR_LIMIT" => "Le nom des hooks d'autorisation doivent faire entre {{min}} et {{max}} caractères",
		"GROUP_NAME_IN_USE" => "Le nom de groupe '{{name}}' est déjà pris",
		"GROUP_DELETION_SUCCESSFUL" => "Le groupe '{{name}}' a été supprimé avec succès",
		"GROUP_CREATION_SUCCESSFUL" => "Le groupe '{{name}}' a été créé avec succès",
		"GROUP_UPDATE" => "Les détails du groupe '{{name}}' ont été mis à jour avec succès.",
		"CANNOT_DELETE_GROUP" => "Le groupe '{{name}}' ne peut pas être supprimé",
		"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "Le groupe '{{name}}' ne peut pas être supprimé car il correspond au groupe par défaut des nouveaux utilisateurs.",
	"GROUP_AUTH_EXISTS" => "Le groupe '{{name}}' a déjà une règle configurée pour le hook '{{hook}}'.",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "La règle du hook '{{hook}}' a été créée pour le groupe '{{name}}'.",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "La règle autorisant l'accès au groupe '{{name}}' pour le hook '{{hook}}' a été mise à jour avec succès.",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "La règle autorisant l'accès au groupe '{{name}}' pour le hook '{{hook}}' a été supprimée avec succès."
));
return $lang;