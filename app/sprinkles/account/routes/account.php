<?php
    
    use Cartalyst\Sentinel\Native\Facades\Sentinel;

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;

    use \Psr\Http\Message\ResponseInterface as Response;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    
    $app->group('/account', function () {
        $this->get('/sign-in-or-register', 'UserFrosting\Controller\AccountController:pageSignInOrRegister')->add('checkEnvironment');
        
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
    