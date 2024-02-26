<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\InventarioService;

class InventarioController
{
	use TokenTrait;
	private readonly InventarioService $inventarioService;
	public function __construct() {
		$this->inventarioService = new InventarioService();
	}
	public function Index(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = $getData["id_tienda"];
			$id_usuario = $this->getUserId($request);
			$data = $this->inventarioService->Traer($id_tienda, $id_usuario);

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
}