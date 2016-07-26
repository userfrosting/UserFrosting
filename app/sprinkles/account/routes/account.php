<?php
    
    use \Psr\Http\Message\ResponseInterface as Response;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    
    $app->group('/account', function () {
        $this->get('/forgot-password', 'UserFrosting\Account\Controller\AccountController:pageForgotPassword');
        
        $this->get('/resend-verification', 'UserFrosting\Account\Controller\AccountController:pageResendVerification');
        
        $this->get('/sign-in-or-register', 'UserFrosting\Account\Controller\AccountController:pageSignInOrRegister')->add('checkEnvironment');
        
        $this->get('/logout', function (Request $request, Response $response, $args) {
            $this->session->destroy();
            $config = $this->config;
            return $response->withStatus(302)->withHeader('Location', $config['site.uri.public']);
        });
        
        $this->post('/register', function (Request $request, Response $response, $args) {            
               
            $e = new \UserFrosting\Support\Exception\BadRequestException();
            $e->addUserMessage("Something bad!");
            throw $e;
            
        });
    });
    