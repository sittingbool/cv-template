<?php

use App\Middleware\CorsMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    // CORS middleware AFTER routing
    $app->add(CorsMiddleware::class);

    // Handle exceptions
    $app->addErrorMiddleware(true, true, true);
};
