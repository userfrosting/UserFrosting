<?php

/**
 * pt_PT
 *
 * PT Portuguese message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Bruno Silva (brunomnsilva@gmail.com)
 *
 */

return [
    "ACCOUNT" => [
        "@TRANSLATION" => "Conta",

        "ACCESS_DENIED" => "Hmm, parece que não tem permissões para fazer isso.",

        "DISABLED" => "Esta conta foi desativada. Por favor contacte-nos para mais informações.",

        "EMAIL_UPDATED" => "Email da conta atualizado",

        "INVALID" => "Esta conta não existe. Pode ter sido removida.  Por favor contacte-nos para mais informações.",

        "MASTER_NOT_EXISTS" => "Não pode registrar uma conta enquanto a conta principal não for criada!",
        "MY"                => "A minha conta",

        "SESSION_COMPROMISED"       => "A sua sessão foi comprometida.  Deverá fechar todas as sessões, voltar a iniciar sessão e verificar que os seus dados não foram alterados por alheios.",
        "SESSION_COMPROMISED_TITLE" => "A sua sessão pode ter sido comprometida",
        "SESSION_EXPIRED"       => "A sua sessão expirou. Por favor inicie nova sessão.",

        "SETTINGS" => [
            "@TRANSLATION"  => "Definições de conta",
            "DESCRIPTION"   => "Atualize as suas definições, incluindo email, nome e password.",
            "UPDATED"       => "Definições de conta atualizadas"
        ],

        "TOOLS" => "Ferramentas de conta",

        "UNVERIFIED" => "A sua conta ainda não foi verificada. Consulte o seu email (incluindo a pasta de spam) para instruções de ativação.",

        "VERIFICATION" => [
            "NEW_LINK_SENT"     => "Enviámos um link de verificação para o endereço {{email}}. Por favor consulte o seu email (incluindo a pasta de spam).",
            "RESEND"            => "Enviar novamente email de verificação",
            "COMPLETE"          => "Verificou com sucesso a sua conta. Pode iniciar sessão.",
            "EMAIL"             => "Por favor introduza o endereço de email que utilizou no registro e um email de verificação será enviado.",
            "PAGE"              => "Reenviar email de verificação para a sua nova conta.",
            "SEND"              => "Enviar email com link de verificação",
            "TOKEN_NOT_FOUND"   => "Token de verificação inexistente / Conta já verificada",
        ]
    ],

    "EMAIL" => [
        "INVALID"   => "Não existe nenhuma conta para <strong>{{email}}</strong>.",
        "IN_USE"    => "O email <strong>{{email}}</strong> já se encontra em uso."
    ],

    "FIRST_NAME" => "Primeiro nome",

    "HEADER_MESSAGE_ROOT" => "INICIOU SESSÃO COM A CONTA ROOT",

    "LAST_NAME" => "Último nome",

    "LOCALE.ACCOUNT" => "Linguagem e localização a utilizar na sua conta",

    "LOGIN" => [
        "@TRANSLATION" => "Entrar",

        "ALREADY_COMPLETE"  => "Sessão já iniciada!",
        "SOCIAL"            => "Ou inicie sessão com",
        "REQUIRED"          => "Lamentamos, tem de iniciar sessão para aceder a este recurso."
    ],

    "LOGOUT" => "Sair",

    "NAME" => "Nome",

    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION"   => "Inicie sessão na sua conta {{site_name}}, ou registre-se para uma nova conta.",
            "SUBTITLE"      => "Registre-se gratuitamente, ou inicie sessão com uma conta existente.",
            "TITLE"         => "Vamos começar!",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION" => "Password",

        "BETWEEN"   => "Entre {{min}}-{{max}} carateres",

        "CONFIRM"               => "Confirme a password",
        "CONFIRM_CURRENT"       => "Por favor confirme a sua password atual",
        "CONFIRM_NEW"           => "Confirmar Nova Password",
        "CONFIRM_NEW_EXPLAIN"   => "Re-introduza a sua nova password",
        "CONFIRM_NEW_HELP"      => "Apenas necessário se escolher uma nova password",
        "CURRENT"               => "Password Atual",
        "CURRENT_EXPLAIN"       => "Tem de confirmar a sua password atual para efetuar alterações",

        "FORGOTTEN" => "Password Esquecida",
        "FORGET" => [
            "@TRANSLATION" => "Esqueci a minha password",

            "COULD_NOT_UPDATE"  => "Não foi possível atualizar a password.",
            "EMAIL"             => "Por favor introduza o endereço de email que utilizou no registro. Enviaremos um email com instruções para efetuar o reset à sua password.",
            "EMAIL_SEND"        => "Enviar email com link de reset da password",
            "INVALID"           => "This password reset request could not be found, or has expired.  Please try <a href=\"{{url}}\">resubmitting your request<a>.",
            "PAGE"              => "Obtenha um link para fazer reset à sua password.",
            "REQUEST_CANNED"    => "Pedido de password esquecida foi cancelado.",
            "REQUEST_SENT"      => "Se o email <strong>{{email}}</strong> corresponder a uma conta em nosso sistema, um link de redefinição de senha será enviado para <strong>{{email}}</strong>."
        ],

        "RESET" => [
            "@TRANSLATION"      => "Reset Password",
            "CHOOSE"            => "Por favor escolha uma nova password para continuar.",
            "PAGE"              => "Escolha uma nova password para a sua conta.",
            "SEND"              => "Definir nova password e registrar"
        ],

        "HASH_FAILED"       => "Falhou o hashing da password. Por favor contacte um administrador do site.",
        "INVALID"           => "A password atual não coincide com a que temos em sistema",
        "NEW"               => "Nova Password",
        "NOTHING_TO_UPDATE" => "Não pode atualizar para a mesma password",
        "UPDATED"           => "Password da conta foi atualizada"
    ],

    "REGISTER"      => "Registrar",
    "REGISTER_ME"   => "Registrar-me",

    "REGISTRATION" => [
        "BROKEN"            => "Lamentamos, existe um problema com o nosso processo de registro.  Contacte-nos diretamente para assistência.",
        "COMPLETE_TYPE1"    => "Registrou-se com sucesso.  Pode iniciar sessão.",
        "COMPLETE_TYPE2"    => "Registrou-se com sucesso. Receberá em breve um email de verificação contendo um link para verificar a sua conta.  Não será possível iniciar sessão até completar este passo.",
        "DISABLED"          => "Lamentamos, o registro de novas contas foi desativado.",
        "LOGOUT"            => "Não pode registrar uma nova conta enquanto tiver sessão iniciada. Por favor feche a sua sessão primeiro.",
        "WELCOME"           => "O registro é rápido e simples."
    ],

    "RATE_LIMIT_EXCEEDED"       => "Excedeu o número de tentativas para esta ação.  Tem de aguardar {{delay}} segundos até lhe ser permitida nova tentativa.",
    "REMEMBER_ME"               => "Lembrar de mim!",
    "REMEMBER_ME_ON_COMPUTER"   => "Lembrar de mim neste computador (não recomendado em computadores públicos)",

    "SIGNIN"                => "Iniciar Sessão",
    "SIGNIN_OR_REGISTER"    => "Iniciar sessão ou registrar",
    "SIGNUP"                => "Registrar",

    "TOS"           => "Termos e Condições",
    "TOS_AGREEMENT" => "Ao registrar uma conta em {{site_title}}, está a aceitar os <a {{link_attributes | raw}}>termos e condições</a>.",
    "TOS_FOR"       => "Termos e Condições para {{title}}",

    "USERNAME" => [
        "@TRANSLATION" => "Nome de utilizador",

        "CHOOSE"  => "Escolha um nome de utilizador único",
        "INVALID" => "Nome de utilizador inválido",
        "IN_USE"  => "O nome de utilizador <strong>{{user_name}}</strong> já se encontra em uso."
    ],

    "USER_ID_INVALID"       => "O id de utilizador solicitado não existe.",
    "USER_OR_EMAIL_INVALID" => "Nome de utilizador ou endereço de email inválidos.",
    "USER_OR_PASS_INVALID"  => "Nome de utilizador ou password inválidos.",

    "WELCOME" => "Bem-vindo, {{first_name}}"
];
