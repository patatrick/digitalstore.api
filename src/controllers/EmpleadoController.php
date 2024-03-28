<?php
namespace App\Controllers;
use App\Models\Empleado;
use App\Services\ComunaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\EmpleadoService;

class EmpleadoController
{
	use TokenTrait;
	private readonly EmpleadoService $_empleadoService;
	private readonly ComunaService $_comunaService;
	public function __construct() {
		$this->_empleadoService = new EmpleadoService();
		$this->_comunaService = new ComunaService();
	}
	public function GetAll(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$data = $this->_empleadoService->GetAll($id_tienda);
			foreach ($data as $key => $value) {
				$cod = $value->cod;
				$data[$key]->cod = null;
				$data[$key]->cod = base64_encode($cod);
			}
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
	public function GetAllAndCommune(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$empleado = $this->_empleadoService->GetAll($id_tienda);
			$comunas = $this->_comunaService->GetAll();
			$roles = [
				[ "id"=>"J", "nombre"=>"Jefe de tienda" ],
				[ "id"=>"C", "nombre"=>"Cajero" ],
			];
			foreach ($empleado as $key => $value) {
				$cod = $value->cod;
				$empleado[$key]->cod = null;
				$empleado[$key]->cod = base64_encode($cod);
			}
			$data = (object) [
				"empleados" => $empleado,
				"comunas" => $comunas,
				"roles" => $roles,
			];
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
	public function GetOne(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$cod = (int) base64_decode($getData["cod"]);
			if (!$cod || $cod == 0) {
				$response->getBody()->write("Código erróneo");
				return $response->withStatus(400);
			}
			$cod = str_pad($cod, 13, '0', STR_PAD_LEFT);
			
			$data = $this->_empleadoService->GetOne($cod, $id_tienda);
			if (!$data) {
				$response->getBody()->write("No se encontró al usuario");
				return $response->withStatus(406);
			}
			$cod = $data->cod;
			$data->cod = null;
			$data->cod = base64_encode($cod);
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
			$id_tienda = (int) $getData["id_tienda"];
			$postData = json_decode(json_encode($request->getParsedBody()));

			$empleado = new Empleado();
			$empleado->ci = trim($postData->ci);
			$empleado->id_tienda = (int) trim($postData->id_tienda);
			$empleado->id_rol = trim($postData->id_rol);
			$empleado->nombre = trim($postData->nombre);
			$empleado->direccion = trim($postData->direccion);
			$empleado->id_comuna = trim($postData->id_comuna);
			$empleado->telefono = trim($postData->telefono);

			if (
				empty(trim($postData->ci)) ||
				empty(trim($postData->id_tienda)) ||
				empty(trim($postData->id_rol)) ||
				empty(trim($postData->nombre)) ||
				empty(trim($postData->direccion)) ||
				empty(trim($postData->id_comuna)) ||
				empty(trim($postData->telefono)))
			{
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			$existeUsuario = $this->_empleadoService->GetOne($empleado->ci, $id_tienda);
			if ($existeUsuario) {
				$response->getBody()->write("Usuario ya existe como trabajador en la tienda!");
				return $response->withStatus(404);
			}
			$exiteCod = true;
			while ($exiteCod) {
				$empleado->cod = null;
				$empleado->cod = $this->GenerateEAN13Aleatorio();
				$exiteCod = $this->_empleadoService->GetOne($empleado->cod, $id_tienda);
			}

			$exito = $this->_empleadoService->Insert($empleado, $id_tienda);
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
	public function Update(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$new_cod = (int) $getData["new_cod"];
			$postData = json_decode(json_encode($request->getParsedBody()));

			$empleado = new Empleado();
			$empleado->ci = trim($postData->ci);
			$empleado->id_tienda = (int) trim($postData->id_tienda);
			$empleado->id_rol = trim($postData->id_rol);
			$empleado->cod = base64_decode($postData->cod);
			$empleado->nombre = trim($postData->nombre);
			$empleado->direccion = trim($postData->direccion);
			$empleado->id_comuna = trim($postData->id_comuna);
			$empleado->telefono = trim($postData->telefono);

			if (
				empty(trim($postData->id_tienda)) ||
				empty(trim($postData->id_rol)) ||
				empty(trim($postData->nombre)) ||
				empty(trim($postData->direccion)) ||
				empty(trim($postData->id_comuna)) ||
				empty(trim($postData->telefono)))
			{
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			if ($new_cod == 1) {
				$exiteCod = true;
				while ($exiteCod) {
					$empleado->cod = null;
					$empleado->cod = $this->GenerateEAN13Aleatorio();
					$exiteCod = $this->_empleadoService->GetOne($empleado->cod, $id_tienda);
				}
			}
			$exito = $this->_empleadoService->Update($empleado, $id_tienda);
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
			$ci = $getData["ci"];

			$exito = $this->_empleadoService->Delete($ci, $id_tienda);
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
}