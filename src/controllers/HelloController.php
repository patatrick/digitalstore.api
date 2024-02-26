<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelloController
{
    public function Index(Request $request, Response $response, array $getData) : Response
    {
        try
        {
            $response->getBody()->write("Hello World!");
            return $response;
        }
        catch (\Throwable $th) {
            $response->getBody()->write($th->getMessage()." in line ".$th->getLine());
            return $response->withStatus(500);
        }
    }
}