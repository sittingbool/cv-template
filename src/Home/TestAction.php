<?php

namespace App\Home;

use App\Document\DocInputModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = DocInputModel::fromRequest($request);
        $response->getBody()->write(json_encode(['application_name' => $input->applicationName]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}