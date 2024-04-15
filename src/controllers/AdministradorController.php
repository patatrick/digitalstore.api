<?php
namespace App\Controllers;
use App\Models\Usuario;
use App\Models\UsuarioTienda;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\AdministradorService;

class AdministradirController
{
	use TokenTrait;
	private readonly AdministradorService $_administradirService;
	public function __construct() {
		$this->_administradirService = new AdministradorService();
	}
	public function GetAll(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			if ($id_tienda < 1) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			$data = $this->_administradirService->GetAll($id_tienda);
			$response->getBody()->write(json_encode([
				"data" => $data,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write("AdministradorController " . $th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Insert(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$postData = json_decode(json_encode($request->getParsedBody()));

			$usuario = new Usuario();
			$usuario->id = (int) $postData["id"];
			$usuario->ci = $postData["ci"];
			$usuario->nombre = $postData["nombre"];
			$usuario->psw = $postData["psw"];
			$usuario->direccion = $postData["direccion"];
			$usuario->id_comuna = $postData["id_comuna"];
			$usuario->telefono = $postData["telefono"];

			$usuarioTienda = new UsuarioTienda();
			$usuarioTienda->id_usuario = $postData["id"];
			$usuarioTienda->id_rol = $postData["id_rol"];
			$usuarioTienda->id_tienda = $id_tienda;
			$usuarioTienda->estado = 1;

			if (
				empty(trim($usuario->id)) ||
				empty(trim($usuario->ci)) ||
				empty(trim($usuario->nombre)) ||
				empty(trim($usuario->direccion)) ||
				empty(trim($usuario->id_comuna)) ||
				empty(trim($usuario->telefono)) ||
				empty(trim($usuarioTienda->id_rol))
			)
			{
				$response->getBody()->write("Campos en blanco.");
				return $response->withStatus(400);
			}

			$id_inserted = $this->_administradirService->Insert($usuario, $usuarioTienda);
			if ($id_inserted > 1) {
				$response->getBody()->write("No se insertaron los datos.");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $id_inserted,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write("AdministradorController " . $th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Update(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$postData = json_decode(json_encode($request->getParsedBody()));
			$id_rol = $postData["id_rol"];
			$usuario = new Usuario();
			$usuario->id = (int) $postData["id"];
			$usuario->ci = $postData["ci"];
			$usuario->cod = $postData["cod"];
			$usuario->nombre = $postData["nombre"];
			$usuario->psw = $postData["psw"];
			$usuario->direccion = $postData["direccion"];
			$usuario->id_comuna = $postData["id_comuna"];
			$usuario->telefono = $postData["telefono"];

			$exito = $this->_administradirService->Update($usuario, $id_rol, $id_tienda);
			if (!$exito) {
				$response->getBody()->write("No se actualizaron los datos.");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write("AdministradorController " . $th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Delete(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$id_usuario = (int) $getData["id_usuario"];
			if ($id_tienda < 1 || $id_usuario < 1) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}

			$exito = $this->_administradirService->Delete($id_usuario, $id_tienda);
			if (!$exito) {
				$response->getBody()->write("No se actualizaron los datos.");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write("AdministradorController " . $th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
}