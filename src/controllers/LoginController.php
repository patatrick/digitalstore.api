<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\LoginService;
use App\Traits\TokenTrait;
use App\Config;

class LoginController
{
	use TokenTrait;
	private $loginService;
	public function Index(Request $request, Response $response, $args) : Response
	{
		try
		{
			$postData = $request->getParsedBody();
			$ci = $postData["ci"] ?? null;
			$psw = $postData["psw"] ?? null;
			if (!$ci || !$psw) {
				$response->getBody()->write("Campos en blanco");
				return $response->withStatus(400);
			}
            $config = new Config();
			$psw = hash('sha256', $psw . $config->hash_semilla);
			$login = new LoginService();
			$userData = $login->Index($ci, $psw);
			if (!$userData) {
				$response->getBody()->write("Usuario o contraseña incorrecta");
				return $response->withStatus(400);
			}
			$session = [
				"id_usuario" => $userData->Usuario->id,
				"tienda" => $userData->Tienda
			];
			$response->getBody()->write(json_encode([
				"data" => $userData,
				"token" => $this->GenerateJWT($session)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
}
