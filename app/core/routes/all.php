<?php

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    global $app;

    // Environment check middleware
    $checkEnvironment = $app->getContainer()['checkEnvironment'];
    
    // Front page
    $app->get('/', function (Request $request, Response $response, $args) {
        $config = $this->config;
        
        return $this->view->render($response, 'pages/index.html.twig');
    })->add($checkEnvironment);

    $app->group('/account', function () use ($checkEnvironment) {
        $this->get('/register', function (Request $request, Response $response, $args) {
            
            return "Nothing";   
        })->add($checkEnvironment);
    });
    
    $app->get('/install', function (Request $request, Response $response, $args) {
        $this->db;
        $schema = Capsule::schema();
        
        if (!$schema->hasTable('activations')) {
            $schema->create('activations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('code');
                $table->boolean('completed')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
    
                $table->engine = 'InnoDB';
            });
        }
        
        if (!$schema->hasTable('persistences')) {
            $schema->create('persistences', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('code');
                $table->timestamps();
    
                $table->engine = 'InnoDB';
                $table->unique('code');
            });
        }
        
        if (!$schema->hasTable('reminders')) {
            $schema->create('reminders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('code');
                $table->boolean('completed')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
        
        if (!$schema->hasTable('roles')) {
            $schema->create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slug');
                $table->string('name');
                $table->text('permissions')->nullable();
                $table->timestamps();
    
                $table->engine = 'InnoDB';
                $table->unique('slug');
            });
        }
        
        if (!$schema->hasTable('role_users')) {
            $schema->create('role_users', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();
                $table->nullableTimestamps();
    
                $table->engine = 'InnoDB';
                $table->primary(['user_id', 'role_id']);
            });
        }
        
        if (!$schema->hasTable('throttle')) {
            $schema->create('throttle', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->nullable();
                $table->string('type');
                $table->string('ip')->nullable();
                $table->timestamps();
    
                $table->engine = 'InnoDB';
                $table->index('user_id');
            });
        }

        if (!$schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email');
                $table->string('password');
                $table->text('permissions')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->timestamps();
    
                $table->engine = 'InnoDB';
                $table->unique('email');
            });
        }
        
        if (!$schema->hasTable('session')) {
            $schema->create('session', function (Blueprint $table) {
                $table->string('id')->unique();
                $table->integer('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity');
            });
        }
    });
    
    // About page
    $app->get('/about', function (Request $request, Response $response, $args) {
        return $this->view->render($response, 'pages/about.html.twig');     
    })->add($checkEnvironment);      
    
    // Flash alert stream
    $app->get('/alerts', function (Request $request, Response $response, $args) {
        return $response->withJson($this->alerts->getAndClearMessages());
    });   
    