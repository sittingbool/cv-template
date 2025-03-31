<?php

use Slim\App;

return function (App $app) {
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->get('/', \App\Home\HomeAction::class);
    $app->post('/test', \App\Home\TestAction::class);
    $app->post('/document', \App\Document\DocumentAction::class);
};
