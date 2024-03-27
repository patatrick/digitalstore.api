<?php
namespace App\Middlewares;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Traits\TokenTrait;
class AuthMiddleware
{
	use TokenTrait;
	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		try
		{
			$response = new Response();
			$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
			if (!$this->DecodeJWT($token)) {
				$response->getBody()->write('Unauthorized');
				return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
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