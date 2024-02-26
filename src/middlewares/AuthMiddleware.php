<?php

namespace App\Middlewares;

use App\Models\TiendaDTO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Traits\TokenTrait;
use Slim\Routing\RouteContext;
use App\Config;

class AuthMiddleware
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
			$tienda = $this->getTienda($request, $id_tienda);

			$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
			if (!$this->DecodeJWT($token)) {
				$response->getBody()->write('Unauthorized');
				return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
			}
			if (!$this->ValidateIp($tienda)) {
				$response->getBody()->write('Forbidden');
				return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
			}
			return $handler->handle($request);
		}
		catch (\Throwable $th) {
			echo $th->getMessage()." on line ".$th->getLine();
			$response->getBody()->write('Unauthorized');
			return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
		}
		
	}
	/** @param TiendaDTO */
	private function ValidateIp($tienda) {
		if (!$tienda) return false;
		if ($tienda->id_rol == "A") return true;

        $config = new Config();
		$response = file_get_contents($config->api["ipinfo"]);
		$data = json_decode($response);
		return $data->ip == $tienda->ip ? true : false;
	}
}