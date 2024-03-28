<?php
namespace App\Controllers;
use App\Models\Producto;
use App\Services\GoogleService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Traits\TokenTrait;

use App\Services\ProductoService;

class ProductoController
{
	use TokenTrait;
	private readonly ProductoService $productoService;
	private readonly GoogleService $googleService;
	public function __construct() {
		$this->productoService = new ProductoService();
		$this->googleService = new GoogleService();
	}
	public function Index(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_usuario = $this->getUserId($request);
			$sku = $getData["sku"];
			$sku = strlen($sku) < 13 ? str_pad($sku, 13, '0', STR_PAD_LEFT) : $sku;
			$id_tienda = $getData["id_tienda"];
			if (!$this->ValidaEAN13($sku)) {
				$response->getBody()->write("Sku desconocido o no válido");
				return $response->withStatus(400);
			}
			
			$data = $this->productoService->Traer($id_usuario, $sku, $id_tienda);
			if (!$data) {
				$arrData = $this->BusquedaGoogle($sku);
				$data = count($arrData) > 0 ? $arrData[0] : new Producto();
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
	public function TraerSku(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_usuario = $this->getUserId($request);
			$id_tienda = $getData["id_tienda"];
			$skuGenerado = $this->SkuUnico($id_tienda);

			$response->getBody()->write(json_encode([
				"data" => $skuGenerado,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function TraerProductoInventario(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_tienda = $getData["id_tienda"];
			$sku = str_pad($getData["sku"], 13, '0', STR_PAD_LEFT);

			if (!$this->ValidaEAN13($sku)) {
				$response->getBody()->write("Sku desconocido o no válido");
				return $response->withStatus(400);
			}
			$producto = $this->productoService->TraerProductoInventario($sku, $id_tienda);
			$response->getBody()->write(json_encode([
				"data" => $producto,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Insertar(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$postData = json_decode(json_encode($request->getParsedBody()));
            $postData->producto->sku = str_pad($postData->producto->sku, 13, '0', STR_PAD_LEFT);
			if ($postData->producto->id === null) {
				$existeSku = $this->productoService->ExisteSkuInterno($postData->inventario->id_tienda, $postData->producto->sku);
				if ($existeSku) {
					$postData->producto->sku = null;
					$postData->inventario->sku = null;
					$postData->inventario->sku = $this->SkuUnico($postData->inventario->id_tienda);
					$postData->producto->sku = $postData->inventario->sku;
				}
			}
			$precioMayor = $postData->inventario->precio_mayor;
			if ($precioMayor == 0) {
				$postData->inventario->precio_mayor = $postData->inventario->precio;
			}
			$id_insertado = $this->productoService->Insertar($postData->producto, $postData->inventario);
			if ($id_insertado === 0) {
				$response->getBody()->write("No se insertaron los datos.");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $id_insertado,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Actualizar(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$postData = json_decode(json_encode($request->getParsedBody()));
			$exito = $this->productoService->Actualizar($postData->producto, $postData->inventario);
			if ($exito === false) {
				$response->getBody()->write("No se actualizaron los datos.");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $exito,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	public function Eliminar(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$id_inventario = $getData['id_inventario'];
			$id_tienda = $getData['id_tienda'];
			$id_usuario = $this->getUserId($request);

			$exito = $this->productoService->Eliminar($id_inventario, $id_tienda, $id_usuario);
			if ($exito === false) {
				$response->getBody()->write("No se eliminaron los datos");
				return $response->withStatus(422);
			}
			$response->getBody()->write(json_encode([
				"data" => $exito,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." in line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	private function SkuUnico($id_tienda) : string
	{
		try
		{
			$existeSku = true;
			$intentos = 0;
			$maxIntentos = 20;  # Número máximo de intentos
			$skuGenerado = str_pad($this->GenerateEAN13Aleatorio(), 13, '0', STR_PAD_LEFT);
			$existeSku = $this->productoService->ExisteSkuInterno($id_tienda, $skuGenerado);

			if ($existeSku || !$this->ValidaEAN13($skuGenerado)) {
				while ($existeSku === true && $intentos < $maxIntentos && !$this->ValidaEAN13($skuGenerado)) {
					$skuGenerado = str_pad($this->GenerateEAN13Aleatorio(), 13, '0', STR_PAD_LEFT);
					$existeSku = $this->productoService->ExisteSkuInterno($id_tienda, $skuGenerado);
					$intentos++;
				}
			}
			if ($intentos === $maxIntentos) {
				throw new \Exception("Error: No se pudo generar un SKU único después de $maxIntentos intentos.");
			}
			return $skuGenerado;
		}
		catch (\Throwable $th) {
			echo "ProductoController " . $th->getMessage()." in line ".$th->getLine();
			http_response_code(500);
			die();
		}
	}
	private function ValidaEAN13(string $sku) : bool
	{
		try
		{
			$sku = str_pad($sku, 13, '0', STR_PAD_LEFT);
			$codigoSinDigito = substr($sku, 0, strlen($sku) - 1);	
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
			return  $codigoSinDigito . $digitoControl == $sku ? true : false;
		}
		catch (\Throwable $th) {
			return false;
		}
	}
	private function BusquedaGoogle(string $sku) : array
	{
		try
		{
			if (strlen(ltrim($sku, '0')) < 8) {
				return [];
			}
			$nombreProducto = $this->googleService->index($sku);
			if ($nombreProducto === null) {
				return [];
			}
			return $this->googleService->GetInfoBySku($nombreProducto, $sku);
		}
		catch (\Throwable $th) {
			echo "ProductoController " . $th->getMessage()." in line ".$th->getLine();
			http_response_code(500);
			die();
		}
	}
}