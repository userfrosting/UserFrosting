<?php

/**
 * pt_BR
 *
 * Brazilian Portuguese message token translations
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n 
 * @author Edu Salgado (@riotbr)
 */

/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Site Content
$lang = array_merge($lang, [
    "REGISTER_WELCOME" => "O registro é rápido e simples.",
    "MENU_USERS" => "Usuários",
    "MENU_CONFIGURATION" => "Configuração",
    "MENU_SITE_SETTINGS" => "Definições do site",
    "MENU_GROUPS" => "Grupos",
    "HEADER_MESSAGE_ROOT" => "VOCÊ ESTÁ CONECTADO COMO USUÁRIO ROOT"
]);

// Installer
$lang = array_merge($lang,array(
    "INSTALLER_INCOMPLETE" => "Você não pode registrar a conta root até que o instalador tenha concluído com êxito!",
    "MASTER_ACCOUNT_EXISTS" => "A conta master já existe!",
    "MASTER_ACCOUNT_NOT_EXISTS" => "Você não pode registrar uma conta até que a conta master tenha sido criada!",
    "CONFIG_TOKEN_MISMATCH" => "Desculpe, este token de configuração não está correto."
));

// Account
$lang = array_merge($lang,array(
    "ACCOUNT_SPECIFY_USERNAME" => "Digite o seu nome.",
    "ACCOUNT_SPECIFY_DISPLAY_NAME" => "Digite o seu nome de exibição.",
    "ACCOUNT_SPECIFY_PASSWORD" => "Digite a sua senha.",
    "ACCOUNT_SPECIFY_EMAIL" => "Digite o seu e-mail.",
    "ACCOUNT_SPECIFY_CAPTCHA" => "Digite o código captcha.",
    "ACCOUNT_SPECIFY_LOCALE" => "Favor especificar uma localidade válida.",
    "ACCOUNT_INVALID_EMAIL" => "E-mail inválido",
    "ACCOUNT_INVALID_USERNAME" => "Nome de usuário inválido",
    "ACCOUNT_INVALID_USER_ID" => "O id do usuário solicitado não existe.",
    "ACCOUNT_USER_OR_EMAIL_INVALID" => "Nome de usuário ou e-mail inválido.",
    "ACCOUNT_USER_OR_PASS_INVALID" => "Nome de usuário ou senha inválido.",
    "ACCOUNT_ALREADY_ACTIVE" => "A sua conta já está ativada.",
    "ACCOUNT_REGISTRATION_DISABLED" => "Lamentamos, mas o registro de conta foi desativado.",
    "ACCOUNT_REGISTRATION_LOGOUT" => "Lamento, mas você não pode registrar uma nova conta enquanto estiver conectado. Por favor, desconecte-se antes.",
    "ACCOUNT_INACTIVE" => "Sua conta está inativa. Confira em suas mensagens/sua caixa de spam as instruções de ativação de conta.",
    "ACCOUNT_DISABLED" => "Sua conta foi desabilitada. Por favor, contate-nos para mais informações.",
    "ACCOUNT_USER_CHAR_LIMIT" => "Seu nome de usuário precisa conter entre {{min}} e {{max}} caracteres de tamanho.",
    "ACCOUNT_DISPLAY_CHAR_LIMIT" => "Seu nome de exibição precisa conter entre {{min}} e {{max}} caracteres de tamanho.",
    "ACCOUNT_PASS_CHAR_LIMIT" => "Sua senha precisa conter entre {{min}} e {{max}} caracteres de tamanho.",
    "ACCOUNT_EMAIL_CHAR_LIMIT" => "Seu e-mail precisa conter entre {{min}} e {{max}} caracteres de tamanho.",
    "ACCOUNT_TITLE_CHAR_LIMIT" => "Títulos precisam conter entre {{min}} e {{max}} caracteres de tamanho.",
    "ACCOUNT_PASS_MISMATCH" => "Sua senha e a confirmação de senha precisam coincidir",
    "ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "O nome de exibição pode incluir caracteres alfanuméricos apenas",
    "ACCOUNT_USERNAME_IN_USE" => "O nome de usuário '{{user_name}}' já está em uso",
    "ACCOUNT_DISPLAYNAME_IN_USE" => "O nome de exibição '{{display_name}}' já está em uso",
    "ACCOUNT_EMAIL_IN_USE" => "O e-mail '{{email}}' já está em uso",
    "ACCOUNT_LINK_ALREADY_SENT" => "Uma mensagem de confirmação já foi enviada para este e-mail no(s) último(s) {{resend_activation_threshold}} segundo(s). Por favor, tente mais tarde.",
    "ACCOUNT_NEW_ACTIVATION_SENT" => "Nós enviamos para você um novo link de ativação. Por favor, confira as suas mensagens",
    "ACCOUNT_SPECIFY_NEW_PASSWORD" => "Por favor, entre a sua nova senha",
    "ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Por favor, confirme a sua nova senha",
    "ACCOUNT_NEW_PASSWORD_LENGTH" => "A nova senha precisa conter entre {{min}} e {{max}} caracteres de tamanho",
    "ACCOUNT_PASSWORD_INVALID" => "A senha atual não coincide com a que temos em nosso registro",
    "ACCOUNT_DETAILS_UPDATED" => "Detalhes de conta atualizados para o usuário '{{user_name}}'",
    "ACCOUNT_CREATION_COMPLETE" => "A conta para o novo usuário '{{user_name}}' foi criada.",
    "ACCOUNT_ACTIVATION_COMPLETE" => "Você ativou com sucesso a sua conta. Você pode conectar-se agora.",
    "ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "Você foi registrado com sucesso. Você pode conectar-se agora.",
    "ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "Você foi registrado com sucesso. Você receberá em breve uma mensagem de ativação. Você precisa ativar a sua conta antes de conectar-se.",
    "ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "Você não pode atualizar com a mesma senha",
    "ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Por favor, confirme a sua senha atual",
    "ACCOUNT_SETTINGS_UPDATED" => "Definições de conta atualizadas",
    "ACCOUNT_PASSWORD_UPDATED" => "Senha de conta atualizada",
    "ACCOUNT_EMAIL_UPDATED" => "E-mail de conta atualizado",
    "ACCOUNT_TOKEN_NOT_FOUND" => "Token não existe / A conta encontra-se ativada",
    "ACCOUNT_USER_INVALID_CHARACTERS" => "O nome de usuário pode conter caracteres alfanuméricos apenas",
    "ACCOUNT_DELETE_MASTER" => "Você não pode apagar a conta master!",
    "ACCOUNT_DISABLE_MASTER" => "Você não pode desabilitar a conta master!",
    "ACCOUNT_DISABLE_SUCCESSFUL" => "A conta do usuário '{{user_name}}' foi desabilitada com sucesso.",
    "ACCOUNT_ENABLE_SUCCESSFUL" => "A conta do usuário '{{user_name}}' foi habilitada com sucesso.",
    "ACCOUNT_DELETION_SUCCESSFUL" => "O usuário '{{user_name}}' foi apagado com sucesso.",
    "ACCOUNT_MANUALLY_ACTIVATED" => "A conta de {{user_name}} foi manualmente ativada",
    "ACCOUNT_DISPLAYNAME_UPDATED" => "O nome de exibição de {{user_name}} mudou para '{{display_name}}'",
    "ACCOUNT_TITLE_UPDATED" => "O título de {{user_name}} mudou para '{{title}}'",
    "ACCOUNT_GROUP_ADDED" => "Usuário adicionado ao grupo '{{name}}'.",
    "ACCOUNT_GROUP_REMOVED" => "Usuário removido do grupo '{{name}}'.",
    "ACCOUNT_GROUP_NOT_MEMBER" => "O usuário não é membro do grupo '{{name}}'.",
    "ACCOUNT_GROUP_ALREADY_MEMBER" => "O usuário já é membro do grupo '{{name}}'.",
    "ACCOUNT_PRIMARY_GROUP_SET" => "Grupo primário definido com sucesso para '{{user_name}}'.",
    "ACCOUNT_WELCOME" => "Bom retorno, {{display_name}}"
));

// Generic validation
$lang = array_merge($lang, array(
    "VALIDATE_REQUIRED" => "O campo '{{self}}' precisa ser especificado.",
    "VALIDATE_BOOLEAN" => "O valor para '{{self}}' precisa ser '0' ou '1'.",
    "VALIDATE_INTEGER" => "O valor para '{{self}}' precisa ser íntegro.",
    "VALIDATE_ARRAY" => "Os valores para '{{self}}' precisam estar em um array."
));

// Configuration
$lang = array_merge($lang,array(
    "CONFIG_PLUGIN_INVALID" => "Você está tentando atualizar as definições do plugin '{{plugin}}', mas não há um plugin com este nome.",
    "CONFIG_SETTING_INVALID" => "Voce está tentando atualizar a definição '{{name}}' para o plugin '{{plugin}}', mas não existe.",
    "CONFIG_NAME_CHAR_LIMIT" => "O nome do site precisa conter entre {{min}} e {{max}} caracteres de tamanho",
    "CONFIG_URL_CHAR_LIMIT" => "A url do site precisa conter entre {{min}} e {{max}} caracteres de tamanho",
    "CONFIG_EMAIL_CHAR_LIMIT" => "O e-mail do site precisa conter entre {{min}} e {{max}} caracteres de tamanho",
    "CONFIG_TITLE_CHAR_LIMIT" => "O novo título do usuário precisa conter entre {{min}} e {{max}} caracteres de tamanho",
    "CONFIG_ACTIVATION_TRUE_FALSE" => "O e-mail de ativação precisa ser `true` (verdadeiro) ou `false` (falso)",
    "CONFIG_REGISTRATION_TRUE_FALSE" => "O registrod e usuário precisa ser `true` (verdadeiro) ou `false` (falso)",
    "CONFIG_ACTIVATION_RESEND_RANGE" => "O limite de ativação precisa estar entre {{min}} e {{max}} horas",
    "CONFIG_EMAIL_INVALID" => "O e-mail informado não é válido",
    "CONFIG_UPDATE_SUCCESSFUL" => "A configuração do seu site foi atualizada. Pode ser necessário carregar uma nova página para que todas as definições tenham efeito",
    "MINIFICATION_SUCCESS" => "CSS e JS minificados e concatenados com sucesso para todos grupos de páginas."
));

// Forgot Password
$lang = array_merge($lang,array(
    "FORGOTPASS_INVALID_TOKEN" => "Seu token se ativação não é válido",
    "FORGOTPASS_OLD_TOKEN" => "Passou o tempo de expiração do token",
    "FORGOTPASS_COULD_NOT_UPDATE" => "Não pode atualizar a senha",
    "FORGOTPASS_NEW_PASS_EMAIL" => "Enviamos uma nova senha por e-mail para você",
    "FORGOTPASS_REQUEST_CANNED" => "A requisição de senha perdida foi cancelada",
    "FORGOTPASS_REQUEST_EXISTS" => "Já existe uma solicitação de senha perdida pendente para esta conta",
    "FORGOTPASS_REQUEST_SUCCESS" => "Enviamos uma mensagem para você explicando como retomar o acesso à sua conta"
));

// Mail
$lang = array_merge($lang,array(
    "MAIL_ERROR" => "Erro fatal tentando enviar mensagem. Contate o seu administrador de servidor",
));

// Miscellaneous
$lang = array_merge($lang,array(
    "PASSWORD_HASH_FAILED" => "O hash de senha falhou. Por favor, contate o administrador do site.",
    "NO_DATA" => "Sem dados/dados falhos enviados",
    "CAPTCHA_FAIL" => "Questão de segurança falha",
    "CONFIRM" => "Confirmar",
    "DENY" => "Negar",
    "SUCCESS" => "Successo",
    "ERROR" => "Erro",
    "SERVER_ERROR" => "Ôpa, parece que seu servidor pode ter cometido um erro. Se você é um administrador, por favor, confira os logs de erro do PHP.",
    "NOTHING_TO_UPDATE" => "Nada para atualizar",
    "SQL_ERROR" => "Erro fatal de SQL",
    "FEATURE_DISABLED" => "Este atributo está desabilitado",
    "ACCESS_DENIED" => "Hmm, parece que você não tem permissões para fazer isso.",
    "LOGIN_REQUIRED" => "Desculpe, mas você precisa estar conectado para acessar este recurso.",
    "LOGIN_ALREADY_COMPLETE" => "Você já está conectado!"
));

// Permissions
$lang = array_merge($lang,array(
    "GROUP_INVALID_ID" => "O id de grupo solicitado não existe",
    "GROUP_NAME_CHAR_LIMIT" => "Nomes de grupos precisam conter entre {{min}} e {{max}} caracteres de tamanho",
    "GROUP_NAME_IN_USE" => "O nome de grupo '{{name}}' já está em uso",
    "GROUP_DELETION_SUCCESSFUL" => "O grupo '{{name}}' foi apagado com sucesso",
    "GROUP_CREATION_SUCCESSFUL" => "O grupo '{{name}}' foi criado com sucesso",
    "GROUP_UPDATE" => "Os detalhes do grupo '{{name}}' foram atualizados com sucesso.",
    "CANNOT_DELETE_GROUP" => "O grupo '{{name}}' não pode ser apagado",
    "GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "O grupo '{{name}}' não pode ser apagado pois está definido como grupo primário padrão para novos usuários. Por favor, selecione antes um grupo primário padrão diferente."
));

return $lang;
