<?php
namespace App\Middlewares;

use App\Config;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Tuupola\Middleware\CorsMiddleware;

class CustomCorsMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = new Config();
        $corsMiddleware = new CorsMiddleware([
            "origin" => (array) $config->cors['url'],
            "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
            "headers.allow" => ["Authorization", "Content-Type"],
            "headers.expose" => [],
            "credentials" => true,
            "origin.server" => null,
            "cache" => 0,
            "error" => function($request, $response, $error) {
                $response->getBody()->write('Forbidden: CORS not allowed');
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            },
            "logger" => null,
        ]);
        return $corsMiddleware->process($request, $handler);
    }
}

