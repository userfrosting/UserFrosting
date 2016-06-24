<?php

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use \Slim\Exception\NotFoundException as NotFoundException;
    
    global $app;
    $config = $app->getContainer()->get('config');
    
    /**
     *  Handle all requests for raw assets.
     */
    $app->get('/' . $config['site.uri.assets-raw'] . '/{url:.+}', function (Request $request, Response $response, $args) {
        $url = $args['url'];
        
        $config = $this->get('config');
        
        // To register the stream wrapper, we must initialize the locator (even though we don't explictly use it)
        $locator = $this->get('locator');
        
        // Remove any query string
        $url = preg_replace('/\?.*/', '', $url);
        
        // Find file
        $abspath = "assets://" . $url;
        
        // Return 404 if file does not exist
        if (!file_exists($abspath)) {
            throw new NotFoundException($request, $response);
        }
        
        $content = file_get_contents($abspath);
        $type = UserFrosting\Util\MimeType::detectByFilename($url);
        $length = filesize($abspath);
        
        return $response
            ->withHeader('Content-Type', $type)
            ->withHeader('Content-Length', $length)
            ->write($content);
    });
    