<?php
namespace App\Controllers;
use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\UsuarioService;
use App\Config;

class UsuarioController
{
	use TokenTrait;
	private readonly UsuarioService $_usuarioService;
	public function __construct() {
		$this->_usuarioService = new UsuarioService();
	}
	public function GetAll(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$data = $this->_usuarioService->GetAll($id_tienda);
			$response->getBody()->write(json_encode([
				"data" => $data,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function GetOneByTienda(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$id_cod_usuario = $getData["id_usuario"];
			$data = $this->_usuarioService->GetOneByTienda($id_tienda, $id_cod_usuario);
			$response->getBody()->write(json_encode([
				"data" => $data,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Insert(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$postData = json_decode(json_encode($request->getParsedBody()));
			$usuario = new Usuario();
			$usuario->ci = !empty(trim($postData->ci)) ? $postData->ci : null;
			$usuario->nombre = !empty(trim($postData->nombre)) ? $postData->nombre : null;
			$usuario->psw = !empty(trim($postData->psw)) ? $postData->psw : null;
			$usuario->direccion = !empty(trim($postData->direccion)) ? $postData->direccion : null;
			$usuario->id_comuna = !empty(trim($postData->id_comuna)) ? $postData->id_comuna : null;
			$usuario->telefono = !empty(trim($postData->telefono)) ? $postData->telefono : null;

			if (!$usuario->ci || !$usuario->nombre || !$usuario->direccion || !$usuario->id_comuna || !$usuario->telefono) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}

			$id_tienda = (int) $getData["id_tienda"];
			$id_rol = trim($getData["id_rol"]);
			$id_rol_session = $this->getRol($request, $id_tienda);
			$rut_sin_verificador = (int) str_replace("-", "", $usuario->ci);

			if ($id_rol_session == "J") {
				if ($id_rol == "A" || $id_rol == "J") {
					$response->getBody()->write("Bad Request");
					return $response->withStatus(400);
				}
			}
			if ($id_rol == "A" || $id_rol == "J") {
                $config = new Config();
				$usuario->psw = hash('sha256', $rut_sin_verificador . $config->hash_semilla);
			}
			$usuario->cod = $this->GenerateEAN13($rut_sin_verificador);

			$id = $this->_usuarioService->Insert($usuario, $id_tienda, $id_rol);
			if (!$id) {
				$response->getBody()->write("Unprocessable Entity");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $id,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Update(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$postData = json_decode(json_encode($request->getParsedBody()));
			$usuario = new Usuario();
			$usuario->ci = !empty(trim($postData->ci)) ? $postData->ci : null;
			$usuario->nombre = !empty(trim($postData->nombre)) ? $postData->nombre : null;
			$usuario->psw = !empty(trim($postData->psw)) ? $postData->psw : null;
			$usuario->direccion = !empty(trim($postData->direccion)) ? $postData->direccion : null;
			$usuario->id_comuna = !empty(trim($postData->id_comuna)) ? $postData->id_comuna : null;
			$usuario->telefono = !empty(trim($postData->telefono)) ? $postData->telefono : null;

			if (!$usuario->ci || !$usuario->nombre || !$usuario->direccion || !$usuario->id_comuna || !$usuario->telefono) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			if ($usuario->id != $this->getUserId($request)) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			$userActual = $this->_usuarioService->GetOne($usuario->ci);
			$usuario->cod = $userActual->cod;
			$usuario->psw = $usuario->psw === null ? $userActual->psw : $usuario->psw;

			$exito = $this->_usuarioService->Update($usuario);
			if (!$exito) {
				$response->getBody()->write("Unprocessable Entity");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function UpdateRol(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$id_usuario = (int) $getData["id_usuario"];
			$id_rol = $getData["id_rol"];
			$id_rol_session = $this->getRol($request, $id_tienda);

			if ($id_rol_session == "J") {
				if ($id_rol == "A" || $id_rol == "J") {
					$response->getBody()->write("Bad Request");
					return $response->withStatus(400);
				}
			}

			$exito = $this->_usuarioService->UpdateRol($id_usuario, $id_tienda, $id_rol);
			if (!$exito) {
				$response->getBody()->write("Unprocessable Entity");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Delete(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$id_usuario = (int) $getData["id_usuario"];
			$estado = $getData["id_estado"] == "true" ? true : false;

			$exito = $this->_usuarioService->Delete($id_usuario, $id_tienda, $estado);
			if (!$exito) {
				$response->getBody()->write("Unprocessable Entity");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	private function GenerateEAN13(int $num) : string
	{
		try
		{
			$codigoSinDigito = $num;
			$codigoSinDigito = str_pad($codigoSinDigito, 12, '0', STR_PAD_LEFT);
			$digitos = str_split($codigoSinDigito);
			$sumaPares = $sumaImpares = 0;
			foreach ($digitos as $indice => $digito) {
				if (($indice % 2) == 0) {
					$sumaPares += $digito;
				} else {
					$sumaImpares += $digito;
				}
			}
			$sumaTotal = $sumaPares + $sumaImpares * 3;
			$digitoControl = (10 - ($sumaTotal % 10)) % 10;
			return  $codigoSinDigito . $digitoControl;
		}
		catch (\Throwable $th) {
			echo "UsuarioController " . $th->getMessage()." in line ".$th->getLine();
			http_response_code(500);
			die();
		}
	}
}