<?php
namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Picqer\Barcode\BarcodeGeneratorSVG;
use App\Traits\TokenTrait;

class SkuController
{
	use TokenTrait;
	public function Index(Request $request, Response $response, array $getData) : Response
	{
		try
		{
			$cantidad_cajas = (int) $getData["cantidad_cajas"];
			$id_tienda = (int) $getData["id_tienda"];
			if (!$cantidad_cajas || !$id_tienda) {
				$response->getBody()->write("Bad Request");
				return $response->withStatus(400);
			}
			$data = [];
			$generator = new BarcodeGeneratorSVG();
            $i = 1;
			foreach ($this->getSku($cantidad_cajas, $id_tienda) as $sku) {
				$barcode = $generator->getBarcode($sku, $generator::TYPE_EAN_13, 3, 100);
                $barcode = str_replace('<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">', "", $barcode);
				$barcodeWithNumbers = <<<HTML
					<div style="display: flex;flex-direction: column; width: fit-content;text-align: center;">
						<strong>Caja $i</strong>
						$barcode
						<strong>$sku</strong>
					</div>
				HTML;
				array_push($data, base64_encode($barcodeWithNumbers));
                $i++;
			}
			
			$response->getBody()->write(json_encode([
				"data" => $data,
				"token" => $this->UpdateJWT($request)
			]));
			return $response;
		}
		catch (\Throwable $th) {
			$response->getBody()->write($th->getMessage()." on line ".$th->getLine());
			return $response->withStatus(500);
		}
	}
	private function getSku(int $cantidad_cajas, int $id_tienda) : array
	{
		$arr = [];
		for ($i=1; $i <= $cantidad_cajas; $i++) { 
			$sku = (int) $id_tienda . str_pad($i, 3, '0', STR_PAD_LEFT);
			array_push($arr, $this->GenerateEAN13($sku));
		}
		return $arr;
	}
}