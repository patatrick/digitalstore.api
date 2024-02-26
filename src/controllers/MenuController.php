<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\MenuService;

class MenuController
{
	use TokenTrait;
	private readonly MenuService $menuService;
	public function __construct()
	{
		$this->menuService = new MenuService();
	}
	public function Index(Request $request, Response $response, $getData) : Response
	{
		try
		{
			$id_usuario = $this->getUserId($request);
            $id_tienda = $getData["id_tienda"];
            if (!$id_tienda) {
                $response->getBody()->write("Bad Request");
                return $response->withStatus(400);
            }
			$data = $this->menuService->Traer($id_usuario, $id_tienda);
			$response->getBody()->write(json_encode([
				"data" => $data,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write("MenuController ".$th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
}