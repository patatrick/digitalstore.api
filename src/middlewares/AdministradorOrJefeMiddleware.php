<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Traits\TokenTrait;
use Slim\Routing\RouteContext;

class AdministradorOrJefeMiddleware
{
	use TokenTrait;
	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		try
		{
			$response = new Response();
			$routeContext = RouteContext::fromRequest($request);
			$route = $routeContext->getRoute();
			$id_tienda = (int) $route->getArgument('id_tienda');
			$id_rol = $this->getRol($request, $id_tienda);
			if ($id_rol != "A" || $id_rol != "J") {
				$response->getBody()->write("No tienes los permisos para este módulo");
				return $response->withStatus(401);
			}
			return $handler->handle($request);
		}
		catch (\Throwable $th) {
			echo $th->getMessage()." on line ".$th->getLine();
			$response->getBody()->write('Unauthorized');
			return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
		}
		
	}
}