<?php
namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GoogleService
{
	private string $url = "https://www.google.com/search?q=";
	public function index(string $sku) : string | null
	{
		try
		{
			$client = new Client();
			$responseGuzzle = $client->get($this->url . $sku);
			if ($responseGuzzle->getStatusCode() != 200) {
				return null;
			}
			return $responseGuzzle->getBody()->getContents();
		}
		catch (RequestException $e) {
			return null;
		}
	}
	public function GetInfoBySku(string $html, string $sku) : array
	{
		$dom = new \DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);
		$arrProducto = [];
		$elementosH3 = $dom->getElementsByTagName('h3');
		foreach ($elementosH3 as $h3) {
			$texto = $h3->textContent;
			$nombre = strrpos($texto, '-') ? substr($texto, 0, strrpos($texto, '-')) : $texto;
			$objProducto = [
				"id"=> null,
				"sku"=> $sku,
				"nombre"=> $nombre,
			];
			array_push($arrProducto, $objProducto);
		}
		return $arrProducto;
	}
}