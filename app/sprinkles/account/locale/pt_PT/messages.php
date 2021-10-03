<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Portuguese message token translations for the 'account' sprinkle.
 *
 * @author Bruno Silva (brunomnsilva@gmail.com)
 * @author José Pedro Machado (V2)
 */
return [
    'ACCOUNT'                 => [
        '@TRANSLATION'        => 'Conta',
        'ACCESS_DENIED'       => 'Hmm, parece que não tem permissões para fazer isso.',
        'DISABLED'            => 'Esta conta foi desativada. Por favor contacte-nos para mais informações.',
        'EMAIL_UPDATED'       => 'Email da conta atualizado',
        'INVALID'             => 'Esta conta não existe. Pode ter sido removida.  Por favor contacte o administrador para mais informações.',
        'MASTER_NOT_EXISTS'   => 'Não pode registar uma conta enquanto a conta principal não for criada!',
        'MY'                  => 'A minha conta',
        'SESSION_COMPROMISED' => [
            '@TRANSLATION' => 'A sua sessão foi comprometida.  Deverá fechar todas as sessões, voltar a iniciar sessão e verificar que os seus dados não foram alterados por alheios.',
            'TITLE'        => 'A sua sessão pode ter sido comprometida',
            'TEXT'         => 'Alguém utilizou o sues dados de login para aceder a esta página. Para sua segurança, todas as sessões foram encerradas. Por favor <a href="{{url}}">faça login de novo</a> e verifique se existe atividade suspeita na sua conta.  É recomendado que modifique a sua password.',
        ],
        'SESSION_EXPIRED'     => 'A sua sessão expirou. Por favor inicie nova sessão.',
        'SETTINGS'            => [
            '@TRANSLATION' => 'Definições de conta',
            'DESCRIPTION'  => 'Atualize as suas definições, incluindo email, nome e password.',
            'UPDATED'      => 'Definições de conta atualizadas',
        ],
        'TOOLS'               => 'Ferramentas de conta',
        'UNVERIFIED'          => 'A sua conta ainda não foi verificada. Consulte o seu email (incluindo a pasta de spam) para instruções de ativação.',
        'VERIFICATION'        => [
            'NEW_LINK_SENT'   => 'Enviámos um link de verificação para o endereço {{email}}. Por favor consulte o seu email (incluindo a pasta de spam).',
            'RESEND'          => 'Enviar novamente email de verificação',
            'COMPLETE'        => 'Verificou com sucesso a sua conta. Pode iniciar sessão.',
            'EMAIL'           => 'Por favor introduza o endereço de email que utilizou no registo e será enviado um email de verificação.',
            'PAGE'            => 'Reenviar email de verificação para a sua nova conta.',
            'SEND'            => 'Enviar email com link de verificação',
            'TOKEN_NOT_FOUND' => 'Código de verificação inexistente / Conta já verificada',
        ],
    ],
    'EMAIL'                   => [
        'INVALID'               => 'Não existe nenhuma conta para <strong>{{email}}</strong>.',
        'IN_USE'                => 'O email <strong>{{email}}</strong> já se encontra em utilização.',
        'VERIFICATION_REQUIRED' => 'Email (verificação necessária - utilize um email real!)',
    ],
    'EMAIL_OR_USERNAME'       => 'Nome de Utilizador ou Email',
    'FIRST_NAME'              => 'Primeiro nome',
    'HEADER_MESSAGE_ROOT'     => 'INICIOU SESSÃO COM A CONTA ROOT',
    'LAST_NAME'               => 'Último nome',
    'LOCALE'                  => [
        'ACCOUNT' => 'Linguagem e localização a utilizar na sua conta',
        'INVALID' => '<strong>{{locale}}</strong> não é uma localização válida.',
    ],
    'LOGIN'                   => [
        '@TRANSLATION'     => 'Entrar',
        'ALREADY_COMPLETE' => 'Sessão já iniciada!',
        'SOCIAL'           => 'Ou inicie sessão com',
        'REQUIRED'         => 'Lamentamos, tem de iniciar sessão para aceder a este recurso.',
    ],
    'LOGOUT'                  => 'Sair',
    'NAME'                    => 'Nome',
    'NAME_AND_EMAIL'          => 'Nome e Email',
    'PAGE'                    => [
        'LOGIN' => [
            'DESCRIPTION' => 'Inicie sessão na sua conta {{site_name}}, ou registe-se para criar uma nova conta.',
            'SUBTITLE'    => 'Registe-se gratuitamente, ou inicie sessão com uma conta existente.',
            'TITLE'       => 'Vamos começar!',
        ],
    ],
    'PASSWORD'                => [
        '@TRANSLATION'        => 'Password',
        'BETWEEN'             => 'Entre {{min}}-{{max}} carateres',
        'CONFIRM'             => 'Confirme a password',
        'CONFIRM_CURRENT'     => 'Por favor confirme a password atual',
        'CONFIRM_NEW'         => 'Confirmar Nova Password',
        'CONFIRM_NEW_EXPLAIN' => 'Re-introduza a sua nova password',
        'CONFIRM_NEW_HELP'    => 'Apenas necessário se escolher uma nova password',
        'CREATE'              => [
            '@TRANSLATION' => 'Criar Password',
            'PAGE'         => 'Escolha uma password para a sua nova conta.',
            'SET'          => 'Defina uma password e faça Login',
        ],
        'CURRENT'             => 'Password Atual',
        'CURRENT_EXPLAIN'     => 'Tem de confirmar a password atual para efetuar alterações',
        'FORGOTTEN'           => 'Recuperar a Password',
        'FORGET'              => [
            '@TRANSLATION'     => 'Esqueci-me da minha password',
            'COULD_NOT_UPDATE' => 'Não foi possível atualizar a password.',
            'EMAIL'            => 'Por favor introduza o endereço de email que utilizou no registo. Enviaremos um email com instruções para redefinir a sua password.',
            'EMAIL_SEND'       => 'Enviar email com link para redefinir a password',
            'INVALID'          => 'A recuperação da sua password falhou ou já expirou. Por favor tente <a href="{{url}}">realizar um novo pedido<a>.',
            'PAGE'             => 'Obtenha um link para fazer redefinir a sua password.',
            'REQUEST_CANNED'   => 'Pedido de password esquecida foi cancelado.',
            'REQUEST_SENT'     => 'Se o email <strong>{{email}}</strong> corresponder a uma conta no nosso sistema, ser-lhe-á enviado um link para redefinir a sua password.',
        ],
        'HASH_FAILED'         => 'A encriptação da password falhou. Por favor contacte um administrador do site.',
        'INVALID'             => 'Password inválida, tente novamente.',
        'NEW'                 => 'Nova Password',
        'NOTHING_TO_UPDATE'   => 'Não pode atualizar para a mesma password',
        'RESET'               => [
            '@TRANSLATION' => 'Redefinir Password',
            'CHOOSE'       => 'Por favor escolha uma nova password para continuar.',
            'PAGE'         => 'Escolha uma nova password para a sua conta.',
            'SEND'         => 'Definir nova password e registar-se',
        ],
        'UPDATED'             => 'Password da conta foi atualizada',
    ],
    'PROFILE'                 => [
        'SETTINGS' => 'Definições do perfil',
        'UPDATED'  => 'Definições do perfil atualizadas',
    ],
    'RATE_LIMIT_EXCEEDED'     => 'Excedeu o número de tentativas para esta ação.  Tem de aguardar {{delay}} segundos até lhe ser permitida nova tentativa.',
    'REGISTER'                => 'Registar',
    'REGISTER_ME'             => 'Registar-me',
    'REGISTRATION'            => [
        'BROKEN'         => 'Lamentamos, existe um problema com o nosso processo de registo.  Contacte-nos diretamente para assistência.',
        'COMPLETE_TYPE1' => 'Registou-se com sucesso.  Pode iniciar sessão.',
        'COMPLETE_TYPE2' => 'Registou-se com sucesso. Receberá em breve um email de verificação contendo um link para verificar a sua conta.  Não será possível iniciar sessão até completar este passo.',
        'DISABLED'       => 'Lamentamos, o registo de novas contas foi desativado.',
        'LOGOUT'         => 'Não pode registar uma nova conta enquanto tiver sessão iniciada. Por favor feche a sua sessão primeiro.',
        'WELCOME'        => 'O registo é rápido e simples.',
    ],
    'REMEMBER_ME'             => 'Manter sessão inicada',
    'REMEMBER_ME_ON_COMPUTER' => 'Manter sessão inicada neste computador (não recomendado em computadores públicos)',
    'SIGN_IN_HERE'            => 'Já tem uma conta? <a href="{{url}}">Inicie sessão aqui</a>',
    'SIGNIN'                  => 'Iniciar Sessão',
    'SIGNIN_OR_REGISTER'      => 'Iniciar sessão ou registar',
    'SIGNUP'                  => 'Registar',
    'TOS'                     => 'Termos e Condições',
    'TOS_AGREEMENT'           => 'Ao registar uma conta em {{site_title}}, está a aceitar os <a {{link_attributes | raw}}>termos e condições</a>.',
    'TOS_FOR'                 => 'Termos e Condições para {{title}}',
    'USERNAME'                => [
        '@TRANSLATION'  => 'Nome de utilizador',
        'CHOOSE'        => 'Escolha um nome de utilizador único',
        'INVALID'       => 'Nome de utilizador inválido',
        'IN_USE'        => 'O nome de utilizador <strong>{{user_name}}</strong> já se encontra em uso.',
        'NOT_AVAILABLE' => 'O nome de utilizador <strong>{{user_name}}</strong> não é válido. Escolha um nome diferente, ou clique em "sugestão".',
    ],
    'USER_ID_INVALID'         => 'O ID de utilizador solicitado não existe.',
    'USER_OR_EMAIL_INVALID'   => 'Nome de utilizador ou endereço de email inválidos.',
    'USER_OR_PASS_INVALID'    => 'Nome de utilizador ou password inválidos.',
    'WELCOME'                 => 'Bem-vindo, {{first_name}}',
];
