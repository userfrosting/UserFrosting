<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Brazilian Portuguese message token translations for the 'account' sprinkle.
 *
 * @author Maxwell Kenned (kenned123@gmail.com)
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION'        => 'Conta',
        'ACCESS_DENIED'       => 'Hmm, parece que você não tem permissão para fazer isso.',
        'DISABLED'            => 'Esta conta foi desativada. Por favor contacte-nos para mais informações.',
        'EMAIL_UPDATED'       => 'Email da conta atualizado',
        'INVALID'             => 'Esta conta não existe. Pode ter sido removida. Por favor contacte-nos para mais informações.',
        'MASTER_NOT_EXISTS'   => 'Não pode registrar uma conta enquanto a conta principal não for criada!',
        'MY'                  => 'A minha conta',
        'SESSION_COMPROMISED' => [
            '@TRANSLATION' => 'A sua sessão foi comprometida. Deverá fechar todas as sessões, voltar a iniciar sessão e verificar se os seus dados não foram alterados por terceiros.',
            'TITLE'        => 'A sua sessão pode ter sido comprometida',
            'TEXT'         => 'Alguém pode ter usado suas informações de login para acessar esta página. Para sua segurança, todas as sessões foram desconectadas. Faça <a href="{{url}}">login</a> e verifique sua conta em busca de atividades suspeitas. Você também pode alterar sua senha.',
        ],
        'SESSION_EXPIRED' => 'A sua sessão expirou. Por favor inicie nova sessão.',
        'SETTINGS'        => [
            '@TRANSLATION' => 'Definições de conta',
            'DESCRIPTION'  => 'Atualize as suas definições, incluindo email, nome e password.',
            'UPDATED'      => 'Definições de conta atualizadas',
        ],
        'TOOLS'        => 'Ferramentas de conta',
        'UNVERIFIED'   => 'A sua conta ainda não foi verificada. Consulte o seu email (incluindo a pasta de spam) para instruções de ativação.',
        'VERIFICATION' => [
            'NEW_LINK_SENT'   => 'Enviamos um link de verificação para o endereço {{email}}. Por favor consulte o seu email (incluindo a pasta de spam).',
            'RESEND'          => 'Enviar novamente email de verificação',
            'COMPLETE'        => 'Verificou com sucesso a sua conta. Pode iniciar sessão.',
            'EMAIL'           => 'Por favor insira o endereço de email que utilizou no registro e um email de verificação será enviado.',
            'PAGE'            => 'Reenviar email de verificação para a sua nova conta.',
            'SEND'            => 'Enviar email com link de verificação',
            'TOKEN_NOT_FOUND' => 'Token de verificação inexistente / Conta já verificada',
        ],
    ],
    'EMAIL' => [
        'INVALID'               => 'Não existe nenhuma conta para <strong>{{email}}</strong>.',
        'IN_USE'                => 'O email <strong>{{email}}</strong> já se encontra em uso.',
        'VERIFICATION_REQUIRED' => 'Email (verificação necessária - use um endereço real!)',
    ],
    'EMAIL_OR_USERNAME'   => 'Usuário ou email',
    'FIRST_NAME'          => 'Primeiro nome',
    'HEADER_MESSAGE_ROOT' => 'INICIOU SESSÃO COM A CONTA ROOT',
    'LAST_NAME'           => 'Sobrenome',
    'LOCALE'              => [
        'ACCOUNT' => 'Linguagem e localização a utilizar na sua conta',
        'INVALID' => '<strong>{{locale}}</strong> não é um código de idioma válido.',
    ],
    'LOGIN' => [
        '@TRANSLATION'     => 'Entrar',
        'ALREADY_COMPLETE' => 'Sessão já iniciada!',
        'SOCIAL'           => 'Ou inicie sessão com',
        'REQUIRED'         => 'Lamentamos, tem de iniciar sessão para acessar este recurso.',
    ],
    'LOGOUT' => 'Sair',
    'NAME'   => 'Nome',
    // 'NAME_AND_EMAIL' => 'Name and email',
    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION' => 'Inicie sessão na sua conta {{site_name}}, ou registre-se para uma nova conta.',
            'SUBTITLE'    => 'Registre-se gratuitamente, ou inicie sessão com uma conta existente.',
            'TITLE'       => 'Vamos começar!',
        ],
    ],
    'PASSWORD' => [
        '@TRANSLATION'        => 'Senha',
        'BETWEEN'             => 'Entre {{min}}-{{max}} carateres',
        'CONFIRM'             => 'Confirme a senha',
        'CONFIRM_CURRENT'     => 'Por favor confirme a sua senha atual',
        'CONFIRM_NEW'         => 'Confirmar Nova Senha',
        'CONFIRM_NEW_EXPLAIN' => 'Re-digite a sua nova senha',
        'CONFIRM_NEW_HELP'    => 'Apenas necessário se escolher uma nova senha',
        'CREATE'              => [
            '@TRANSLATION' => 'Criar Senha',
            'PAGE'         => 'Escolha uma senha para sua nova conta.',
            'SET'          => 'Digite a senha e Entre!',
        ],
        'CURRENT'         => 'Senha Atual',
        'CURRENT_EXPLAIN' => 'Tem de confirmar a sua senha atual para efetuar alterações',
        'FORGOTTEN'       => 'Senha Esquecida',
        'FORGET'          => [
            '@TRANSLATION'     => 'Esqueci a minha senha',
            'COULD_NOT_UPDATE' => 'Não foi possível atualizar a senha.',
            'EMAIL'            => 'Por favor digite o endereço de email que utilizou no registro. Enviaremos um email com instruções para efetuar a redefinição da sua senha.',
            'EMAIL_SEND'       => 'Enviar email com link de redefinição da senha',
            'INVALID'          => 'Não foi possível encontrar essa solicitação de redefinição de senha ou expirou. Por favor tente <a href="{{url}}">reenviar a solicitação<a>.',
            'PAGE'             => 'Obtenha um link para fazer reset à sua senha.',
            'REQUEST_CANNED'   => 'Pedido de senha esquecida foi cancelado.',
            'REQUEST_SENT'     => 'Se o email <strong>{{email}}</strong> corresponder a uma conta em nosso sistema, um link de redefinição de senha será enviado para <strong>{{email}}</strong>.',
        ],
        'HASH_FAILED'       => 'Falhou o hashing da senha. Por favor contacte um administrador do site.',
        'INVALID'           => 'A senha atual não coincide com a que temos em sistema',
        'NEW'               => 'Nova Password',
        'NOTHING_TO_UPDATE' => 'Não pode atualizar para a mesma senha',
        'RESET'             => [
            '@TRANSLATION' => 'Redefinir Senha',
            'CHOOSE'       => 'Por favor escolha uma nova senha para continuar.',
            'PAGE'         => 'Escolha uma nova senha para a sua conta.',
            'SEND'         => 'Definir nova senha e registrar',
        ],
        'UPDATED' => 'Senha da conta foi atualizada',
    ],
    'PROFILE' => [
        'SETTINGS' => 'Configurações de perfil',
        'UPDATED'  => 'Configurações de perfil atualizadas',
    ],
    'RATE_LIMIT_EXCEEDED' => 'Excedeu o número de tentativas para esta ação. Tem de aguardar {{delay}} segundos para uma nova tentativa.',
    'REGISTER'            => 'Registrar',
    'REGISTER_ME'         => 'Registrar-me',
    'REGISTRATION'        => [
        'BROKEN'         => 'Lamentamos, existe um problema com o nosso processo de registro. Contacte-nos diretamente para assistência.',
        'COMPLETE_TYPE1' => 'Registrou-se com sucesso. Pode iniciar sessão.',
        'COMPLETE_TYPE2' => 'Registrou-se com sucesso. Receberá em breve um email contendo um link para verificar a sua conta. Não será possível iniciar sessão até completar este passo.',
        'DISABLED'       => 'Lamentamos, o registro de novas contas foi desativado.',
        'LOGOUT'         => 'Não pode registrar uma nova conta enquanto tiver sessão iniciada. Por favor feche a sua sessão primeiro.',
        'WELCOME'        => 'O registro é simples e rápido.',
    ],
    'REMEMBER_ME'             => 'Lembrar de mim!',
    'REMEMBER_ME_ON_COMPUTER' => 'Lembrar computador (não recomendado em computadores públicos)',
    'SIGN_IN_HERE'            => 'Já tem uma conta? <a href="{{url}}">Entre aqui.</a>',
    'SIGNIN'                  => 'Iniciar Sessão',
    'SIGNIN_OR_REGISTER'      => 'Iniciar sessão ou registrar',
    'SIGNUP'                  => 'Registrar',
    'TOS'                     => 'Termos e Condições',
    'TOS_AGREEMENT'           => 'Ao registrar uma conta em {{site_title}}, estará aceitando os <a {{link_attributes | raw}}>termos e condições</a>.',
    'TOS_FOR'                 => 'Termos e Condições para {{title}}',
    'USERNAME'                => [
        '@TRANSLATION'  => 'Nome de usuário',
        'CHOOSE'        => 'Escolha um nome de usuário único',
        'INVALID'       => 'Nome de usuário inválido',
        'IN_USE'        => 'O nome de usuário <strong>{{user_name}}</strong> já se encontra em uso.',
        'NOT_AVAILABLE' => 'Usuário <strong>{{user_name}}</strong> não é válido. Escolha um nome diferente, ou clique em \'sugestão\'.',
    ],
    'USER_ID_INVALID'       => 'O id de usuário solicitado não existe.',
    'USER_OR_EMAIL_INVALID' => 'Nome de usuário ou endereço de email inválidos.',
    'USER_OR_PASS_INVALID'  => 'Nome de usuário ou senha inválidos.',
    'WELCOME'               => 'Bem-vindo, {{first_name}}',
];
