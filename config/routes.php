<?php

use Slim\App;

$i18nDeText = file_get_contents(__DIR__ . '/../files/i18n/de.json');
$i18nEnText = file_get_contents(__DIR__ . '/../files/i18n/en.json');
$i18n = [
    'de' => json_decode($i18nDeText, true),
    'en' => json_decode($i18nEnText, true)
];
$_ENV['i18n'] = $i18n;

return function (App $app) {
    // OPTIONS handler MUST be here, not in middleware.php
    $app->options('/{routes:.+}', function ($request, $response) {
        return $response;
    });

    $app->get('/', \App\Home\HomeAction::class);
    $app->post('/test', \App\Home\TestAction::class);
    $app->post('/document', \App\Document\DocumentAction::class);
};
