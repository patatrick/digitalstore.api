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
            $headers = [
                'headers' => [
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                    "Referer"=> "https://www.google.cl/",
                    "Accept"=> "*/*",
                    "Accept-Encoding"=> "gzip, deflate, br",
                    'Accept-Language' => 'es-419,es;q=0.8',
                    "Sec-Ch-Ua"=> "Not A(Brand\";v=\"99\", \"Brave\";v=\"121\", \"Chromium\";v=\"121",
                    "Sec-Ch-Ua-Mobile"=> "?0",
                    "Sec-Ch-Ua-Model"=> "",
                    "Sec-Ch-Ua-Platform"=> "Windows",
                    "Sec-Ch-Ua-Platform-Version"=> "15.0.0",
                    "Sec-Fetch-Dest"=> "document",
                    "Sec-Fetch-Mode"=> "navigate",
                    "Sec-Fetch-Site"=> "same-origin",
                    "Sec-Fetch-User"=> "?1",
                    "Sec-Gpc"=> "1",
                    "Upgrade-Insecure-Requests"=> "1",
                ],
            ];
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