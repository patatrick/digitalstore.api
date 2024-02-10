<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\LoginService;
use App\Traits\TokenTrait;
use const App\HASH_SEMILLA;

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
			$psw = hash('sha256', $psw . HASH_SEMILLA);
			$login = new LoginService();
			$userData = $login->Index($ci, $psw);
			if (!$userData) {
				$response->getBody()->write("Usuario o contraseña incorrecta");
				return $response->withStatus(400);
			}

			$playload = $this->GetPlayload($userData->Usuario->id, $userData->Usuario->id_rol);
			$response->getBody()->write(json_encode([
				"data" => $userData,
				"token" => $this->GenerateJWT($playload)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	private function GetPlayload($idUsuario, $idRol) : array
	{
		return [
			"id_usuario" => $idUsuario,
			"id_rol" => $idRol
		];
	}
}
