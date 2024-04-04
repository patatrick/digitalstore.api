<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Caja;
use App\Services\LoginService;
use App\Services\VentaService;

use App\Config;
use App\Traits\SkuTrait;
use App\Traits\TokenTrait;

class LoginController
{
	use TokenTrait;
	use SkuTrait;
	private readonly LoginService $_loginService;
	private readonly Config $_config;
	private readonly VentaService $_ventaService;
	public function __construct() {
		$this->_loginService = new LoginService();
		$this->_config = new Config();
		$this->_ventaService = new VentaService();
	}
	public function Index(Request $request, Response $response, $args) : Response
	{
		try
		{
			$postData = $request->getParsedBody();
			$ci = empty(trim($postData["ci"])) ? null : trim($postData["ci"]);
			$psw = empty(trim($postData["psw"])) ? null : trim($postData["psw"]);
			if (!$ci || !$psw) {
				$response->getBody()->write("Campos en blanco");
				return $response->withStatus(400);
			}
			$psw = hash('sha256', $psw . $this->_config->hash_semilla);
			$userData = $this->_loginService->Index($ci, $psw);
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
			$response->getBody()->write($th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function LoginVenta(Request $request, Response $response, $args) : Response
	{
		try
		{
			$postData = $request->getParsedBody();
			$sku = empty(trim($postData["sku"])) ? null : trim($postData["sku"]);
			$sku_jefeTienda = empty(trim($postData["sku_jefeTienda"])) ? null : trim($postData["sku_jefeTienda"]);
			if (!$sku_jefeTienda || !$sku  || !$this->ValidateSKU($sku) || !$this->ValidateSKU($sku_jefeTienda)) {
				$response->getBody()->write("Código incorrecto");
				return $response->withStatus(400);
			}
			$sku = str_pad($sku, 13, '0', STR_PAD_LEFT);
			$sku_jefeTienda = str_pad($sku_jefeTienda, 13, '0', STR_PAD_LEFT);

			$id_tienda = $this->getTiendaIdBySku($sku);
			$nro_caja = $this->getNroCajaBySku($sku);
			if ($id_tienda < 1 || $nro_caja < 1) {
				$response->getBody()->write("Código incorrecto");
				return $response->withStatus(400);
			}
			$ip = $request->getServerParams()['REMOTE_ADDR'];
			$tienda = $this->_loginService->LoginVenta($id_tienda, $nro_caja, $ip, $sku_jefeTienda);
			if (!$tienda) {
				$response->getBody()->write("No se pudo autenticar. Verifica los datos ingresados y asegúrate de estar en la tienda.");
				return $response->withStatus(404);
			}
			$caja = new Caja();
			$caja->sku_caja = $sku;
			$caja->cod_jefe_tienda = $sku_jefeTienda;
			$caja->id_tienda = $id_tienda;
			$id_caja = $this->_ventaService->OpenCaja($caja);
			$session = [
				"nro_caja" => $nro_caja,
				"id_caja" => $id_caja,
				"tienda" => $tienda,
				"id_usuario" => null
			];
			$response->getBody()->write(json_encode([
				"data" => $session,
				"token" => $this->GenerateJWT($session, true)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function LoginCajero(Request $request, Response $response, $getData) : Response
	{
		try
		{
			$postData = $request->getParsedBody();
			$sku = empty(trim($postData["sku"])) ? null : trim($postData["sku"]);
			if (!$sku || !$this->ValidateSKU($sku)) {
				$response->getBody()->write("Código incorrecto");
				return $response->withStatus(400);
			}

			$id_tienda = (int) $getData['id_tienda'];
			$ip = $request->getServerParams()['REMOTE_ADDR'];

			$data = $this->_loginService->LoginCajero($sku, $id_tienda, $ip);
			if (!$data) {
				$response->getBody()->write("No se pudo autenticar. Verifica el código ingresado y asegúrate de estar en la tienda.");
				return $response->withStatus(404);
			}
			$nro_caja = $this->getNroCaja($request);
			$objTienda = $this->getTienda($request, $id_tienda);

			$session = [
				"nro_caja" => $nro_caja,
				"id_caja" => $this->getIdCaja($request),
				"tienda" => $objTienda,
				"id_usuario" => $data->ci
			];
			$response->getBody()->write(json_encode([
				"data" => [
					"empleado"=> $data,
					"nro_caja"=> $nro_caja,
					"tienda"=> $objTienda,
				],
				"token" => $this->GenerateJWT($session, true)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function CloseCaja(Request $request, Response $response, $getData) : Response
	{
		try
		{
			$postData = $request->getParsedBody();
			$sku_jefeTienda = empty(trim($postData["sku_jefeTienda"])) ? null : trim($postData["sku_jefeTienda"]);
			if (!$sku_jefeTienda || !$this->ValidateSKU($sku_jefeTienda)) {
				$response->getBody()->write("Código incorrecto");
				return $response->withStatus(400);
			}
			$data = $this->_ventaService->CerrarCaja($this->getIdCaja($request));
			if (!$data) {
				$response->getBody()->write("No se pudo cerrar la caja, intente nuevamente");
				return $response->withStatus(422);
			}

			$response->getBody()->write(json_encode([
				"data" => null,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
}
