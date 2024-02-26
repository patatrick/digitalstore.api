<?php
namespace App\Controllers;
use App\Models\Proveedor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\ProveedorService;

class ProveedorController
{
	use TokenTrait;
	private readonly ProveedorService $_proveedorService;
	public function __construct() {
		$this->_proveedorService = new ProveedorService();
	}
	public function GetAll(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData["id_tienda"];
			$data = $this->_proveedorService->GetAll($id_tienda);
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
			
			$proveedor = new Proveedor();
			$proveedor->descripcion = trim($postData->descripcion) ? $postData->descripcion : null;
			$proveedor->id_tienda = $postData->id_tienda ? $postData->id_tienda : null;
			$proveedor->nombre = trim($postData->nombre) ? $postData->nombre : null;
			$proveedor->rut = trim($postData->rut) ? $postData->rut : null;
			$proveedor->telefono = trim($postData->telefono) ? $postData->telefono : null;

			if (
				!$proveedor->id_tienda ||
				!$proveedor->nombre ||
				!$proveedor->rut ||
				!$proveedor->telefono
			)
			{
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}

			$idNew = $this->_proveedorService->Insert($proveedor);
            if (!$idNew) {
				$response->getBody()->write("Unprocessable Entity");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $idNew,
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