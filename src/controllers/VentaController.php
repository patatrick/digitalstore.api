<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\VentaService;

class VentaController
{
	use TokenTrait;
	private readonly VentaService $_ventaService;
	public function __construct() {
		$this->_ventaService = new VentaService();
	}
	public function Insertar(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$postData = json_decode(json_encode($request->getParsedBody()));
			$postData->id_vendedor = $this->getUserId($request);

			$data = $this->_ventaService->Insertar($postData);
			if ($data === false) {
				$response->getBody()->write("No se insertaron los datos");
				return $response->withStatus(400);
			}
			$response->getBody()->write(json_encode([
				"data" => true,
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