<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\InformeService;

class InformeController
{
	use TokenTrait;
	private readonly InformeService $_informeService;
	public function __construct() {
		$this->_informeService = new InformeService();
	}
	public function ObtenerVentas(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = (int) $getData['id_tienda'];
			$tipo = (int) $getData['tipo'];
			$id_usuario = $this->getUserId($request);
			$data = $this->_informeService->Ventas($id_usuario, $id_tienda, $this->Where($tipo));
			if (!$data) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
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
	private function Where(int $tipo) : string
	{
		switch ($tipo) {
			# Diaria
			case 1: return "WHERE v.ingreso >= CURDATE() AND v.ingreso < CURDATE() + INTERVAL 1 DAY";
			# Semanal
			case 2: return "WHERE v.ingreso >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY AND v.ingreso < CURDATE() + INTERVAL 1 WEEK - INTERVAL WEEKDAY(CURDATE()) DAY";
			# Mensual
			case 3: return "WHERE YEAR(v.ingreso) = YEAR(CURDATE()) AND MONTH(v.ingreso) = MONTH(CURDATE())";
			# Anual
			case 4: return "WHERE YEAR(v.ingreso) = YEAR(CURDATE())";
			default : return "";
			// PARA ALGÚN RANGO DE FECHAS - WHERE v.ingreso >= '2024-01-01' AND v.ingreso <= '2024-02-29'
		}
	}
}