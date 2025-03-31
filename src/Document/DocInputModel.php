<?php
namespace App\Document;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocInputModel
{
    public mixed $parsedBody;

    public function __construct(Request $request)
    {
        $this->parsedBody = $request->getParsedBody();
    }

    public function __get($p): mixed
    {
        if (!$this->parsedBody) {
            return null;
        }
        if (array_key_exists($p, $this->parsedBody)) {
            return $this->parsedBody[$p];
        }
        user_error("undefined property $p");
        return null;
    }

    public static function fromRequest(Request $request): DocInputModel
    {
        return new DocInputModel($request);
    }
}
